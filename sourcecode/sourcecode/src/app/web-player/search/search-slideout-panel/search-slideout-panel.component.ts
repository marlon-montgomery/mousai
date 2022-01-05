import {Component, OnDestroy, OnInit, ViewEncapsulation} from '@angular/core';
import {SearchSlideoutPanel} from "./search-slideout-panel.service";
import {WebPlayerUrls} from "../../web-player-urls.service";
import {Subscription} from "rxjs";
import {NavigationStart, Router} from "@angular/router";
import {Player} from "../../player/player.service";
import {Track} from "../../../models/Track";
import {filter} from "rxjs/operators";
import {BrowserEvents} from "@common/core/services/browser-events.service";
import {WebPlayerImagesService} from '../../web-player-images.service';

@Component({
    selector: 'search-slideout-panel',
    templateUrl: './search-slideout-panel.component.html',
    styleUrls: ['./search-slideout-panel.component.scss'],
    encapsulation: ViewEncapsulation.None,
    host: {'[class.open]': 'panel.isOpen'}
})
export class SearchSlideoutPanelComponent implements OnInit, OnDestroy {
    private subscriptions: Subscription[] = [];

    constructor(
        public panel: SearchSlideoutPanel,
        public urls: WebPlayerUrls,
        public images: WebPlayerImagesService,
        private router: Router,
        private player: Player,
    ) {}

    ngOnInit() {
        this.bindToRouter();
    }

    ngOnDestroy() {
        this.subscriptions.forEach(subscription => {
            subscription.unsubscribe();
        });
        this.subscriptions = [];
    }

    /**
     * Play specified track.
     */
    public playTrack(track: Track) {
        this.player.queue.prepend([track]);
        this.player.cueTrack(track).then(() => {
            this.player.play();
        });
    }

    /**
     * Pause the player.
     */
    public pausePlayer() {
        this.player.pause();
    }

    /**
     * Go to specified track's page.
     */
    public goToTrackPage(track: Track) {
        this.router.navigate(this.urls.track(track));
    }

    /**
     * Close search panel when navigation to different route occurs.
     */
    private bindToRouter() {
        const sub = this.router.events
            .pipe(filter(e => e instanceof NavigationStart))
            .subscribe(() => this.panel.close());

        this.subscriptions.push(sub);
    }
}
