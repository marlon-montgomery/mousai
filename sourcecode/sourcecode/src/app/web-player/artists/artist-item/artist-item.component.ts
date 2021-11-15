import {Component, Input} from '@angular/core';
import {WebPlayerUrls} from '../../web-player-urls.service';
import {Player} from '../../player/player.service';
import {queueId} from '../../player/queue-id';
import {Artist} from '../../../models/Artist';

@Component({
    selector: 'artist-item',
    templateUrl: './artist-item.component.html',
    styleUrls: ['./artist-item.component.scss'],
    host: {'class': 'media-grid-item', '[class.active]': 'playing()'},
})
export class ArtistItemComponent {
    @Input() scrollContainer: HTMLElement;
    @Input() artist: Artist;
    @Input() disablePlayback = false;

    constructor(
        public urls: WebPlayerUrls,
        private player: Player,
    ) {}

    public playing(): boolean {
        return this.player.mediaItemPlaying(this.queueId());
    }

    public play() {
        this.player.playMediaItem(this.queueId());
    }

    public pause() {
        this.player.pause();
    }

    public queueId() {
        return queueId(this.artist, 'allTracks');
    }
}
