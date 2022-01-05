import {Component, Input, ViewEncapsulation} from '@angular/core';
import {WebPlayerUrls} from '../../web-player-urls.service';
import {Album} from '../../../models/Album';
import {Albums} from '../albums.service';
import {Player} from '../../player/player.service';
import {queueId} from '../../player/queue-id';

@Component({
    selector: 'album-item',
    templateUrl: './album-item.component.html',
    styleUrls: ['./album-item.component.scss'],
    encapsulation: ViewEncapsulation.None,
    host: {'class': 'media-grid-item', '[class.active]': 'playing()'},
})
export class AlbumItemComponent {
    @Input() album: Album;
    @Input() scrollContainer: HTMLElement;
    @Input() disablePlayback = false;

    constructor(
        public urls: WebPlayerUrls,
        private albums: Albums,
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
        return queueId(this.album, 'allTracks');
    }
}
