import {Component, OnDestroy, OnInit} from '@angular/core';
import {DatatableService} from '@common/datatable/datatable.service';
import {Track} from '../../../models/Track';
import {Player} from '../../player/player.service';
import {Subscription} from 'rxjs';

@Component({
    selector: 'fullscreen-overlay-queue',
    templateUrl: './fullscreen-overlay-queue.component.html',
    styleUrls: ['./fullscreen-overlay-queue.component.scss'],
    providers: [DatatableService],
})
export class FullscreenOverlayQueueComponent implements OnInit, OnDestroy {
    private subscription: Subscription;
    constructor(
        public datatable: DatatableService<Track>,
        public player: Player,
    ) {}

    ngOnInit() {
        this.datatable.init({
            disableSort: true,
        });
        this.subscription = this.player.queue.shuffledQueue$.subscribe(tracks => {
            this.datatable.data = tracks;
        });
    }

    ngOnDestroy() {
        this.subscription.unsubscribe();
    }
}
