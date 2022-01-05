import {Component, OnDestroy, OnInit} from '@angular/core';
import {ActivatedRoute} from '@angular/router';
import {Album} from '../../../models/Album';
import {WebPlayerUrls} from '../../web-player-urls.service';
import {FormattedDuration} from '../../player/formatted-duration.service';
import {Player} from '../../player/player.service';
import {AlbumContextMenuComponent} from '../album-context-menu/album-context-menu.component';
import {WpUtils} from '../../web-player-utils';
import {ContextMenu} from '@common/core/ui/context-menu/context-menu.service';
import {queueId} from '../../player/queue-id';
import {UserLibrary} from '../../users/user-library/user-library.service';
import {Track} from '../../../models/Track';
import {Tracks} from '../../tracks/tracks.service';
import {Subscription} from 'rxjs';
import {DatatableService} from '@common/datatable/datatable.service';
import {Settings} from '@common/core/config/settings.service';
import {CurrentUser} from '@common/auth/current-user';
import {TrackCommentsService} from '../../tracks/track-comments.service';

@Component({
    selector: 'album',
    templateUrl: './album.component.html',
    styleUrls: ['./album.component.scss'],
    providers: [DatatableService],
})
export class AlbumComponent implements OnInit, OnDestroy {
    public album: Album;
    public totalDuration: string;
    public adding = false;
    private deleteTrackSub: Subscription;

    constructor(
        private route: ActivatedRoute,
        public urls: WebPlayerUrls,
        private duration: FormattedDuration,
        private player: Player,
        private contextMenu: ContextMenu,
        public library: UserLibrary,
        private tracksApi: Tracks,
        public datatable: DatatableService<Track>,
        public settings: Settings,
        public currentUser: CurrentUser,
        public trackComments: TrackCommentsService,
    ) {}

    ngOnInit() {
        this.route.data.subscribe(data => {
            this.setAlbum(data.api.album);
            this.datatable.init({
                initialData: this.album.tracks,
            });
            const total = this.album.tracks.reduce((t, track) => t + track.duration, 0);
            this.totalDuration = this.duration.fromMilliseconds(total);

            if (this.settings.get('player.track_comments')) {
                this.trackComments.setMediaItem(this.album);
                this.trackComments.pagination$.next(data.api.comments);
            }
        });

        this.deleteTrackSub = this.tracksApi.tracksDeleted$.subscribe(trackIds => {
            this.datatable.data = this.datatable.data.filter(track => {
                return !trackIds.includes(track.id);
            });
        });
    }

    ngOnDestroy() {
        this.deleteTrackSub.unsubscribe();
    }

    public toggleLike() {
        this.adding = true;
        const promise = this.library.has(this.album) ?
            this.library.remove([this.album]) :
            this.library.add([this.album]);
        promise.then(() => {
            this.adding = false;
        });
    }

    public queueId() {
        return queueId(this.album, 'allTracks');
    }

    public openContextMenu(e: MouseEvent) {
        e.stopPropagation();
        this.contextMenu.open(
            AlbumContextMenuComponent,
            e.target,
            {data: {item: this.album, type: 'album'}},
        );
    }

    private setAlbum(album: Album) {
        const simplifiedAlbum = Object.assign({}, album, {tracks: []});
        album.tracks = WpUtils.assignAlbumToTracks(album.tracks, simplifiedAlbum);
        this.album = album;
    }
}
