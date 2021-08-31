import {
    Component,
    ElementRef,
    EventEmitter,
    Input,
    NgZone,
    OnDestroy,
    OnInit,
    Output
} from '@angular/core';
import {Track} from '../../../models/Track';
import {Player} from '../../player/player.service';
import {FormattedDuration} from '../../player/formatted-duration.service';
import {Album} from '../../../models/Album';
import {WpUtils} from '../../web-player-utils';
import {WebPlayerUrls} from '../../web-player-urls.service';
import {TrackContextMenuComponent} from '../track-context-menu/track-context-menu.component';
import {PlaylistTrackContextMenuComponent} from '../../playlists/playlist-track-context-menu/playlist-track-context-menu.component';
import {SelectedTracks} from './selected-tracks.service';
import {Subscription} from 'rxjs';
import {WebPlayerState} from '../../web-player-state.service';
import {BrowserEvents} from '@common/core/services/browser-events.service';
import {ContextMenu} from '@common/core/ui/context-menu/context-menu.service';
import {CdkDragDrop} from '@angular/cdk/drag-drop';
import {A, DELETE} from '@angular/cdk/keycodes';
import {DatatableService} from '@common/datatable/datatable.service';

@Component({
    selector: 'track-table',
    templateUrl: './track-table.component.html',
    styleUrls: ['./track-table.component.scss'],
    providers: [SelectedTracks],
})
export class TrackTableComponent implements OnInit, OnDestroy {
    protected subscriptions: Subscription[] = [];

    @Input() dataSource: DatatableService<Track>;
    @Input() album: Album;

    @Input() showTrackImage = true;
    @Input() showArtist = false;
    @Input() showAlbum = false;
    @Input() showPopularity = false;
    @Input() showAddedAt = false;
    @Input() showHeader = true;
    @Input() overrideQueue = true;

    @Input() queueItemId: string;
    @Input() contextMenuParams = {type: 'track', extra: {}};
    @Input() select: Track;

    @Output() delete = new EventEmitter();
    @Output() orderChanged: EventEmitter<CdkDragDrop<any>> = new EventEmitter();

    public reorderEnabled = false;

    constructor(
        public player: Player,
        private duration: FormattedDuration,
        public urls: WebPlayerUrls,
        private contextMenu: ContextMenu,
        private zone: NgZone,
        private el: ElementRef,
        public selectedTracks: SelectedTracks,
        private browserEvents: BrowserEvents,
        public state: WebPlayerState,
    ) {}

    ngOnInit() {
        this.bindHammerEvents();
        this.bindKeyboardShortcuts();
        this.reorderEnabled = !!this.orderChanged.observers.length && !this.state.isMobile;
        if (this.select) {
            this.selectedTracks.add(this.select);
        }
    }

    ngOnDestroy() {
        this.subscriptions.forEach(subscription => {
            subscription.unsubscribe();
        });
        this.subscriptions = [];
    }

    public getTracks(): Track[] {
        return this.dataSource.data$.value;
    }

    public trackIsPlaying(track: Track) {
        return this.player.isPlaying() && this.player.cued(track);
    }

    public playTrack(track: Track, index: number) {
        if (this.player.cued(track)) {
            this.player.play();
        } else {
            this.playFrom(index);
        }
    }

    public async toggleTrackPlayback(track: Track, index: number) {
        if (this.trackIsPlaying(track)) {
            this.player.pause();
        } else {
            this.playTrack(track, index);
        }
    }

    public showContextMenu(track: Track, e: MouseEvent) {
        e.stopPropagation();
        e.preventDefault();
        this.contextMenu.open(this.getContextMenuComponent(), e.target, {data: this.getContextMenuParams(track)});
    }

    /**
     * Get params needed to open context menu for track.
     */
    public getContextMenuParams(track: Track) {
        return Object.assign(
            {item: track, type: this.contextMenuParams.type, selectedTracks: this.selectedTracks},
            this.contextMenuParams.extra
        );
    }

    /**
     * Get context menu component based on specified type.
     */
    private getContextMenuComponent() {
        if (this.contextMenuParams.type === 'playlistTrack') {
            return PlaylistTrackContextMenuComponent;
        } else {
            return TrackContextMenuComponent;
        }
    }

    /**
     * Add tracks from specified index to player queue and start playback.
     */
    private playFrom(index: number) {
        if (this.overrideQueue) {
            let tracks = this.getTracks().slice(index, this.getTracks().length);
            tracks = WpUtils.assignAlbumToTracks(tracks, this.album);

            this.player.overrideQueue({tracks, queuedItemId: this.queueItemId}).then(() => {
                this.player.play();
            });
        } else {
            this.player.stop();
            this.player.queue.set(index);
            this.player.play();
        }
    }

    public formatTrackDuration(track: Track) {
        return this.duration.fromMilliseconds(track.duration);
    }

    /**
     * Bind handlers to needed hammer.js events.
     */
    private bindHammerEvents() {
        let hammer, singleTap, doubleTap;

        this.zone.runOutsideAngular(() => {
            hammer = new Hammer.Manager(this.el.nativeElement);
            singleTap = new Hammer.Tap({event: 'singletap'});
            doubleTap = new Hammer.Tap({event: 'doubletap', taps: 2});
            hammer.add([doubleTap, singleTap]);
        });

        // select track on tap or multiple tracks when ctrl is pressed
        hammer.on('singletap', (e: HammerInput) => {
            this.zone.run(() => {
                const data = this.getTrackFromEvent(e);

                if ( ! data || ! data.track) return;

                if (this.state.isMobile && !e.target.closest('.track-options-button')) {
                    const i = this.getTracks().findIndex(t => t.id === data.track.id);
                    this.toggleTrackPlayback(data.track, i);
                }

                if ( ! e.srcEvent.ctrlKey) {
                    this.selectedTracks.clear();
                    this.selectedTracks.add(data.track);
                } else {
                    this.selectedTracks.toggle(data.track);
                }
            });
        });

        // play track on double tap
        hammer.on('doubletap', e => {
            this.zone.run(() => {
                const data = this.getTrackFromEvent(e);
                if ( ! data) return;
                this.playTrack(data.track, data.index);
            });
        });

        // deselect all tracks when clicked outside of track list.
        const sub = this.browserEvents.globalClick$.subscribe(e => {
            if ( ! (e.target as HTMLElement).closest('.track-list-row')) {
                this.selectedTracks.clear();
            }
        });

        this.subscriptions.push(sub);
    }

    /**
     * Get track from specified hammer tap event.
     */
    private getTrackFromEvent(e: HammerInput): {track: Track, index: number} {
        if ( ! e.target) return;
        const row = e.target.closest('.track-list-row');
        if ( ! row) return;
        const id = +row.getAttribute('data-id');
        const i = this.getTracks().findIndex(track => track.id === id);
        return {track: this.getTracks()[i], index: i};
    }

    /**
     * Initiate tracks list shortcuts.
     */
    private bindKeyboardShortcuts() {
        const sub = this.browserEvents.globalKeyDown$.subscribe((e: KeyboardEvent) => {
            // ctrl+a - select all tracks
            if (e.ctrlKey && e.keyCode === A) {
                this.getTracks().forEach(track => this.selectedTracks.add(track));
                e.preventDefault();

            // delete - fire delete event
            } else if (e.keyCode === DELETE && ! this.selectedTracks.empty()) {
                this.delete.emit(this.selectedTracks.all());
                this.selectedTracks.clear();
                e.preventDefault();
            }
        });

        this.subscriptions.push(sub);
    }

    public trackByFn = (i: number, track: Track) => track.id;
}
