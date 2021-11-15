import {PlaybackStrategy} from './playback-strategy.interface';
import {PlayerState} from '../player-state.service';
import {Track} from '../../../models/Track';
import {Injectable, NgZone} from '@angular/core';
import {PlayerQueue} from '../player-queue.service';
import {WebPlayerImagesService} from '../../web-player-images.service';
import {requestFullScreen} from '@common/core/utils/request-full-screen';

@Injectable({
    providedIn: 'root'
})
export class Html5Strategy implements PlaybackStrategy {

    /**
     * Whether player is already bootstrapped.
     */
    private bootstrapped = false;

    /**
     * Volume that should be set after player is bootstrapped.
     * Number between 1 and 100.
     */
    private pendingVolume: number = null;

    /**
     * Html5 video element instance.
     */
    private html5: HTMLVideoElement;

    /**
     * Track currently cued for playback.
     */
    private cuedTrack: Track;

    /**
     * Track that is currently being cued.
     */
    private cueing: Track;

    constructor(
        private state: PlayerState,
        private zone: NgZone,
        private queue: PlayerQueue,
        private wpImages: WebPlayerImagesService,
    ) {}

    public play() {
        this.cueTrack(this.queue.getCurrent()).then(() => {
            this.html5.play().then(() => {
                this.state.playing = true;
            }, () => {
                this.state.playing = false;
            });
        });
    }

    public pause() {
        this.html5.pause();
        this.state.playing = false;
    }

    public stop() {
        this.pause();
        this.seekTo(0);
        this.state.playing = false;
    }

    public seekTo(time: number) {
        if (time === Infinity) return;
        this.html5.currentTime = time;
    }

    /**
     * Get loaded track duration in seconds.
     */
    public getDuration() {
        return (this.html5 && this.html5.seekable.length) > 0 ?
            this.html5.seekable.end(0) :
            0;
    }

    /**
     * Get elapsed time in seconds since the track started playing
     */
    public getCurrentTime() {
        return this.html5 ? this.html5.currentTime : 0;
    }

    /**
     * Set html5 player volume to float between 0 and 1.
     */
    public setVolume(volume: number) {
        if ( ! this.html5) {
            this.pendingVolume = volume;
        } else {
            this.html5.volume = volume / 100;
        }
    }

    public mute() {
        this.html5.muted = true;
    }

    public unMute() {
        this.html5.muted = false;
    }

    public getCuedTrack(): Track {
        return this.cuedTrack;
    }

    public isReady() {
        return this.bootstrapped;
    }

    public cueTrack(track: Track): Promise<any> {
        const cuedTrack = this.cueing || this.cuedTrack;
        if (cuedTrack && cuedTrack.id === track?.id) {
            return Promise.resolve();
        }

        this.cueing = track;

        this.state.buffering = true;

        this.bootstrap();

        this.html5.src = track.url;
        this.html5.poster = this.wpImages.getTrackImage(track);
        this.cuedTrack = track;
        this.cueing = null;
        return Promise.resolve();
    }

    public maximize() {
        requestFullScreen(this.html5);
    }

    public destroy() {
        this.html5 && this.html5.remove();
        this.html5 = null;
        this.bootstrapped = false;
        this.cuedTrack = null;
    }

    private bootstrap() {
        if (this.bootstrapped) return;

        this.html5 = document.createElement('video');
        this.html5.setAttribute('playsinline', 'true');
        this.html5.setAttribute('oncontextmenu', 'return false;');
        this.html5.setAttribute('controlsList', 'nodownload');
        this.html5.id = 'html5-player';
        document.querySelector('.html5-player').appendChild(this.html5);

        this.handlePlayerReadyEvent();
        this.handlePlayerStateChangeEvents();

        this.bootstrapped = true;
    }

    private handlePlayerStateChangeEvents() {
        this.html5.addEventListener('ended', () => {
            this.state.firePlaybackEnded();
            this.setState('playing', false);
        });

        this.html5.addEventListener('playing', () => {
            this.setState('playing', true);
        });

        this.html5.addEventListener('pause', () => {
            this.setState('playing', false);
        });

        this.html5.addEventListener('error', () => {
            this.cuedTrack = null;
            this.setState('playing', false);
            this.state.firePlaybackEnded();
        });
    }

    private setState(name: string, value: boolean) {
        this.zone.run(() => this.state[name] = value);
    }

    private handlePlayerReadyEvent(resolve?) {
        if (this.state.muted) this.mute();
        this.bootstrapped = true;
        resolve && resolve();
        this.state.fireReadyEvent();

        if (this.pendingVolume) {
            this.setVolume(this.pendingVolume);
            this.pendingVolume = null;
        }
    }
}
