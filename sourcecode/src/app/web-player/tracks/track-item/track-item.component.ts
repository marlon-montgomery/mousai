import {Component, Input} from '@angular/core';
import {WebPlayerUrls} from '../../web-player-urls.service';
import {Player} from '../../player/player.service';
import {queueId} from '../../player/queue-id';
import {Track} from '../../../models/Track';

@Component({
    selector: 'track-item',
    templateUrl: './track-item.component.html',
    styleUrls: ['./track-item.component.scss'],
    host: {class: 'media-grid-item', '[class.active]': 'playing()'},
})
export class TrackItemComponent {
    @Input() track: Track;
    @Input() disablePlayback = false;

    constructor(
        public urls: WebPlayerUrls,
        private player: Player,
    ) {}

    public playing() {
        return this.player.mediaItemPlaying(this.queueId());
    }

    public async play() {
        this.player.playMediaItem(this.queueId(), [this.track]);
    }

    public pause() {
        this.player.pause();
    }

    public queueId() {
        return queueId(this.track, 'allTracks');
    }
}
