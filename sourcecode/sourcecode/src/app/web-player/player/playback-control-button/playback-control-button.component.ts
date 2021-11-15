import {Component, EventEmitter, Input, Output, ViewEncapsulation} from '@angular/core';
import {Player} from '../player.service';
import {Track} from '../../../models/Track';

@Component({
    selector: 'playback-control-button',
    templateUrl: './playback-control-button.component.html',
    styleUrls: ['./playback-control-button.component.scss'],
    encapsulation: ViewEncapsulation.None
})
export class PlaybackControlButtonComponent {
    @Output() play = new EventEmitter();
    @Output() pause = new EventEmitter();
    @Input() track: Track;
    @Input() playing = null;

    constructor(
        private player: Player,
    ) {}

    public trackIsPlaying() {
        // parent component is controlling playback state.
        if (this.playing !== null) return this.playing;

        // playback state is based on current track
        return (this.player.isPlaying() || this.player.isBuffering()) && this.player.cued(this.track);
    }
}
