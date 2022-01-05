import {Component, ElementRef, NgZone, ViewEncapsulation} from '@angular/core';
import {BasePlayerSeekbar} from './base-player-seekbar';
import {FormattedDuration} from '../../formatted-duration.service';
import {Player} from '../../player.service';

@Component({
    selector: 'player-seekbar',
    templateUrl: './player-seekbar.component.html',
    styleUrls: ['./player-seekbar.component.scss'],
    encapsulation: ViewEncapsulation.None
})
export class PlayerSeekbarComponent extends BasePlayerSeekbar {
    constructor(
        protected el: ElementRef<HTMLElement>,
        protected duration: FormattedDuration,
        protected player: Player,
        protected zone: NgZone,
    ) {
        super();
    }
}
