import {
    AfterViewInit,
    Component,
    ElementRef,
    Inject,
    NgZone,
    OnDestroy,
    OnInit,
    ViewChild,
    ViewEncapsulation
} from '@angular/core';
import {ContextMenu} from '@common/core/ui/context-menu/context-menu.service';
import {DOCUMENT} from '@angular/common';
import {Player} from '../player/player.service';
import {PlayerQueue} from '../player/player-queue.service';
import {FullscreenOverlay} from './fullscreen-overlay.service';
import {BrowserEvents} from '@common/core/services/browser-events.service';
import {WebPlayerState} from '../web-player-state.service';
import {Subscription} from 'rxjs';
import {Track} from '../../models/Track';
import {TrackContextMenuComponent} from '../tracks/track-context-menu/track-context-menu.component';
import {ESCAPE} from '@angular/cdk/keycodes';

@Component({
    selector: 'fullscreen-overlay',
    templateUrl: './fullscreen-overlay.component.html',
    styleUrls: ['./fullscreen-overlay.component.scss'],
    encapsulation: ViewEncapsulation.None,
    host: {
        '[class.maximized]': 'overlay.isMaximized()',
        class: 'fullscreen-overlay',
    }
})
export class FullscreenOverlayComponent implements OnInit, AfterViewInit, OnDestroy {
    @ViewChild('videoContainer') videoContainer: ElementRef<HTMLElement>;
    public subscription: Subscription;

    constructor(
        public player: Player,
        private el: ElementRef,
        public queue: PlayerQueue,
        private contextMenu: ContextMenu,
        public overlay: FullscreenOverlay,
        private browserEvents: BrowserEvents,
        public state: WebPlayerState,
        private zone: NgZone,
        @Inject(DOCUMENT) private document: Document
    ) {}

    ngOnInit() {
        this.subscription = this.browserEvents.globalKeyDown$.subscribe(e => {
            // minimize overlay on ESC key press.
            if (e.keyCode === ESCAPE) {
                this.overlay.minimize();
            }
        });
    }

    ngAfterViewInit() {
        this.bindHammer();
    }

    ngOnDestroy() {
        this.subscription.unsubscribe();
        this.subscription = null;
    }

    /**
     * Get current track in player queue.
     */
    public getCurrent() {
        return this.queue.getCurrent();
    }

    /**
     * Get previous track in player queue.
     */
    public getPrevious() {
        return this.queue.getPrevious() || this.getCurrent();
    }

    /**
     * Get next track in player queue.
     */
    public getNext() {
        return this.queue.getNext() || this.getCurrent();
    }

    /**
     * Open track context menu.
     */
    public openTrackContextMenu(track: Track, e: MouseEvent) {
        e.stopPropagation();

        this.contextMenu.open(
            TrackContextMenuComponent,
            e.target,
            {data: {item: track, type: 'track'}}
        );
    }

    /**
     * Exit browser fullscreen mode or minimize the overlay.
     */
    public minimize() {
        if (this.isBrowserFullscreen()) {
            this.exitBrowserFullscreen();
        } else {
            this.overlay.minimize();
        }
    }

    /**
     * Toggle browser fullscreen mode.
     */
    public toggleBrowserFullscreen() {
        // in browser full screen mode, exit it
        if (this.isBrowserFullscreen()) {
            return this.exitBrowserFullscreen();
        // in bemusic fullscreen mode, enter browser fullscreen mode
        } else if (this.overlay.isMaximized()) {
            this.player.cued() && this.player.maximize();
        // not fullscreen at all yet, enter bemusic fullscreen mode
        } else {
            this.player.cued() && this.overlay.maximize();
        }
    }

    /**
     * Exit browser fullscreen mode.
     */
    public exitBrowserFullscreen() {
        if (this.document.exitFullscreen) {
            this.document.exitFullscreen();
        } else if (this.document.webkitExitFullscreen) {
            this.document.webkitExitFullscreen();
        } else if (this.document.mozCancelFullScreen) {
            this.document.mozCancelFullScreen();
        } else if (this.document.msExitFullscreen) {
            this.document.msExitFullscreen();
        }
    }

    /**
     * Check if browser fullscreen mode is active.
     */
    public isBrowserFullscreen() {
        return this.document.fullscreenElement ||
        this.document.webkitFullscreenElement ||
        this.document.mozFullscreenElement ||
        this.document.msFullScreenElement;
    }

    private bindHammer() {
        let hammer, doubleTap;
        this.zone.runOutsideAngular(() => {
            hammer = new Hammer.Manager(this.videoContainer.nativeElement);
            doubleTap = new Hammer.Tap({event: 'doubletap', taps: 2});
            hammer.add(doubleTap);
        });

        hammer.on('doubletap', e => {
            // ignore clicks on play button
            if ( ! e.target.closest('playback-control-button')) {
                this.zone.run(() => {
                    this.toggleBrowserFullscreen();
                });
            }
        });
    }
}
