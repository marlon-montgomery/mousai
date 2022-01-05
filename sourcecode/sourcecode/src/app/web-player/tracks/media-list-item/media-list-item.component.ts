import {
    ChangeDetectorRef,
    Component,
    ElementRef,
    Input,
    OnChanges,
    SimpleChanges
} from '@angular/core';
import {Track} from '../../../models/Track';
import {Player} from '../../player/player.service';
import {TrackContextMenuComponent} from '../track-context-menu/track-context-menu.component';
import {ContextMenu} from '@common/core/ui/context-menu/context-menu.service';
import {Album, ALBUM_MODEL} from '../../../models/Album';
import {User} from '@common/core/types/models/User';
import {WebPlayerUrls} from '../../web-player-urls.service';
import {AlbumContextMenuComponent} from '../../albums/album-context-menu/album-context-menu.component';
import {ComponentType} from '@angular/cdk/portal';
import {queueId} from '../../player/queue-id';
import {WpUtils} from '../../web-player-utils';
import {Settings} from '@common/core/config/settings.service';
import {TrackCommentsService} from '../track-comments.service';
import {CurrentUser} from '@common/auth/current-user';
import {Playlist, PLAYLIST_MODEL} from '../../../models/Playlist';
import {PlaylistContextMenuComponent} from '../../playlists/playlist-context-menu/playlist-context-menu.component';
import {Genre} from '../../../models/Genre';
import {Artist} from '../../../models/Artist';
import {trackIsLocallyUploaded} from '../../utils/track-is-locally-uploaded';

type Media = (Track | Album | Playlist) & {tracks?: Track[], genres?: Genre[], created_at_relative?: string, release_date?: string};

@Component({
    selector: 'media-list-item',
    templateUrl: './media-list-item.component.html',
    styleUrls: ['./media-list-item.component.scss'],
    providers: [TrackCommentsService],
})
export class MediaListItemComponent implements OnChanges {
    @Input() media: Media;
    @Input() tracks: Track[];
    @Input() showArtwork = true;
    @Input() showActions = true;
    @Input() reposter: User;
    @Input() linksInNewTab = false;

    public seekbarType: 'line'|'waveform';
    public artists: (Artist|User)[];
    public activeTrack: Track;
    public userLoggedIn: boolean;

    constructor(
        public player: Player,
        private contextMenu: ContextMenu,
        private el: ElementRef<HTMLElement>,
        public urls: WebPlayerUrls,
        private settings: Settings,
        public trackComments: TrackCommentsService,
        private currentUser: CurrentUser,
        private cd: ChangeDetectorRef,
    ) {
        this.seekbarType = this.settings.get('player.seekbar_type');
        this.userLoggedIn = this.currentUser.isLoggedIn();
    }

    ngOnChanges(changes: SimpleChanges) {
        if (changes.media && changes.media.currentValue) {
            if (this.media.model_type === ALBUM_MODEL) {
                this.artists = this.media.artists;
                this.activeTrack = this.media.tracks && this.media.tracks[0];
                this.el.nativeElement.classList.add('is-album');
                this.media.tracks = WpUtils.assignAlbumToTracks(this.media.tracks, this.media);
            } else if (this.media.model_type === PLAYLIST_MODEL) {
                this.activeTrack = this.media.tracks && this.media.tracks[0];
                this.artists = this.media.editors;
                this.el.nativeElement.classList.add('is-album');
            } else {
                this.artists = this.media.artists;
                this.activeTrack = this.media;
                this.el.nativeElement.classList.add('is-track');
            }
            this.trackComments.mediaItem = this.activeTrack;
        }
    }

    public isAlbum(media?: Media): media is Album {
        if ( ! media) media = this.media;
        return media?.model_type === ALBUM_MODEL;
    }

    public play(track: Track) {
        this.activeTrack = track;
        this.trackComments.mediaItem = this.activeTrack;
        if (this.media.model_type === ALBUM_MODEL) {
            this.player.playMediaItem(this.albumQueueId(this.media), this.media.tracks, track);
            this.cd.detectChanges();
        } else if (this.tracks) {
            const index = this.tracks.findIndex(t => t.id === this.media.id);
            const tracks = this.tracks.slice(index, this.tracks.length);
            this.player.overrideQueue({tracks}).then(() => {
                this.player.play();
            });
        } else {
            if ( ! this.player.queue.has(track)) {
                this.player.queue.prepend([track]);
            }
            this.player.cueTrack(track).then(() => {
                this.player.play();
            });
        }
    }

    public albumQueueId(album: Album) {
        return queueId(album, 'allTracks');
    }

    public playing(track: Track) {
        return this.player.state.playing && this.player.cued(track);
    }

    public showContextMenu(media: Media, e: MouseEvent) {
        e.stopPropagation();
        let component: ComponentType<any>;
        if (this.media.model_type === ALBUM_MODEL) {
            component = AlbumContextMenuComponent;
        } else if (this.media.model_type === PLAYLIST_MODEL) {
            component = PlaylistContextMenuComponent;
        } else {
            component = TrackContextMenuComponent;
        }
        this.contextMenu.open(component, e.target, {data: {item: media}});
    }

    public mediaLink() {
        if (this.media.model_type === ALBUM_MODEL) {
            return this.urls.album(this.media);
        } else if (this.media.model_type === PLAYLIST_MODEL) {
            return this.urls.playlist(this.media);
        } else {
            return this.urls.track(this.media);
        }
    }

    public shouldShowWave() {
        return this.settings.get('player.seekbar_type') === 'waveform' && trackIsLocallyUploaded(this.activeTrack);
    }
}
