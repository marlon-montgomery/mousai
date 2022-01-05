import {Component, Input, ViewEncapsulation} from '@angular/core';
import {WebPlayerUrls} from '../../web-player-urls.service';
import {Player} from '../../player/player.service';
import {Playlist} from '../../../models/Playlist';
import {User} from '@common/core/types/models/User';
import {queueId} from '../../player/queue-id';

@Component({
    selector: 'playlist-item',
    templateUrl: './playlist-item.component.html',
    styleUrls: ['./playlist-item.component.scss'],
    encapsulation: ViewEncapsulation.None,
    host: {'class': 'media-grid-item', '[class.active]': 'playing()'},
})
export class PlaylistItemComponent {
    @Input() scrollContainer: HTMLElement;
    @Input() playlist: Playlist;
    @Input() creator: User;

    constructor(
        public urls: WebPlayerUrls,
        private player: Player,
    ) {}

    public playing() {
        return this.player.mediaItemPlaying(this.queueId());
    }

    public play() {
        this.player.playMediaItem(this.queueId());
    }

    public pause() {
        this.player.pause();
    }

    public queueId() {
        return queueId(this.playlist, 'allTracks');
    }
}
