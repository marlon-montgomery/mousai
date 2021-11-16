import {Injectable, NgZone} from '@angular/core';
import {PlayerQueue} from '../player-queue.service';
import {Track} from '../../../models/Track';
import {Search} from '../../search/search.service';
import {PlayerState} from '../player-state.service';
import {PlaybackStrategy} from './playback-strategy.interface';
import {Settings} from '@common/core/config/settings.service';
import {LazyLoaderService} from '@common/core/utils/lazy-loader.service';
import {requestFullScreen} from '@common/core/utils/request-full-screen';
import {AppHttpClient} from '@common/core/http/app-http-client.service';
import {catchError, tap} from 'rxjs/operators';

@Injectable({
    providedIn: 'root'
})
export class YoutubeStrategy implements PlaybackStrategy {
    private bootstrapped = false;
    private bootstrapping: Promise<any>;
    private pendingVolume: number|null = null;
    private youtube: YT.Player;
    private tracksSkippedDueToError = 0;

    private activeTrack: Track;
    private searchResults: ({title: string, id: string}[])|null = null;

    constructor(
        private queue: PlayerQueue,
        private state: PlayerState,
        private search: Search,
        private zone: NgZone,
        private settings: Settings,
        private lazyLoader: LazyLoaderService,
        private http: AppHttpClient,
    ) {}

    public play() {
        this.cueTrack(this.queue.getCurrent()).then(() => {
            this.youtube.playVideo();
            this.state.playing = true;
        }, () => {
            this.state.playing = false;
        });
    }

    public pause() {
        this.youtube?.pauseVideo();
        this.state.playing = false;
    }

    public stop() {
        this.youtube.stopVideo();
        this.state.playing = false;
    }

    public seekTo(time: number) {
        this.youtube && this.youtube.seekTo && this.youtube.seekTo(time, true);
    }

    public getDuration(): number {
        return this.youtube ?
            (this.youtube.getDuration ? this.youtube.getDuration() : 0)
            : 0;
    }

    public getCurrentTime(): number {
        return this.youtube ? this.youtube.getCurrentTime() : 0;
    }

    public setVolume(number: number) {
        if ( ! this.youtube || ! this.youtube.setVolume) {
            this.pendingVolume = number;
        } else {
            this.youtube.setVolume(number);
        }
    }

    public mute() {
        this.youtube && this.youtube.mute && this.youtube.mute();
    }

    public unMute() {
        this.youtube && this.youtube.unMute && this.youtube.unMute();
    }

    public getCuedTrack(): Track {
        return this.activeTrack;
    }

    public isReady() {
        return this.bootstrapped;
    }

    public async cueTrack(track: Track): Promise<any> {
        if (this.activeTrack === track) return;

        // clear search results, so old search results are not used for new track
        this.searchResults = null;
        this.activeTrack = track;
        this.setState('buffering', true);

        if ( ! track.youtube_id) {
            await this.searchYoutubeForVideoMatches();
            this.assignFirstSearchResult();
        }

        return this.bootstrap(track.youtube_id).then(() => {
            this.cueYoutubeVideo(track);
        });
    }

    public destroy() {
        try {
            this.youtube && this.youtube.destroy();
        } catch (e) {
            //
        }
        this.youtube = null;
        this.bootstrapped = false;
        this.activeTrack = null;
        this.searchResults = null;
    }

    private cueYoutubeVideo(track: Track) {
        if (track.youtube_id && track.youtube_id !== this.getYoutubeId()) {
            const suggestedQuality = this.settings.get('youtube.suggested_quality');
            this.youtube.cueVideoById({videoId: track.youtube_id, suggestedQuality});
        }
        this.activeTrack = track;
    }

    private getYoutubeId(): string {
        const url = this.youtube.getVideoUrl();
        return url && url.split('v=')[1];
    }

    private assignFirstSearchResult() {
        if (this.searchResults && this.activeTrack) {
            this.activeTrack.youtube_id = this.searchResults[0]?.id;
            this.searchResults.shift();
        }
    }

    private searchYoutubeForVideoMatches() {
        const artist = this.activeTrack?.artists?.[0]?.name ||
            this.activeTrack?.album?.artists?.[0]?.name;
        return this.search.videoId(artist, this.activeTrack)
            .pipe(
                catchError(() => []),
                tap(results => this.searchResults = results)
            ).toPromise();
    }

    private bootstrap(videoId: string): Promise<any> {
        if (this.bootstrapped) return new Promise<void>(resolve => resolve());
        if (this.bootstrapping) return this.bootstrapping;

        this.lazyLoader.loadAsset('https://www.youtube.com/iframe_api', {type: 'js'});

        this.bootstrapping = new Promise((resolve, reject) => {
            if (window['onYouTubeIframeAPIReady']) {
                return this.initYoutubePlayer(videoId, resolve);
            } else {
                window['onYouTubeIframeAPIReady'] = () => {
                    this.initYoutubePlayer(videoId, resolve);
                };
            }
        });

        return this.bootstrapping;
    }

    private initYoutubePlayer(videoId: string, resolve) {
        this.youtube = new YT.Player('youtube-player', {
            videoId,
            playerVars: this.getPlayerVars(),
            events: {
                onReady: () => this.onYoutubeReady(resolve),
                onError: this.onYoutubeError.bind(this),
                onStateChange: this.onYoutubeStateChange.bind(this)
            }
        });
    }

    private onYoutubeReady(resolve) {
        if (this.state.muted) this.mute();
        this.bootstrapped = true;
        this.bootstrapping = null;
        resolve();
        this.state.fireReadyEvent();

        if (this.pendingVolume) {
            this.setVolume(this.pendingVolume);
            this.pendingVolume = null;
        }
    }

    private async onYoutubeError(e: YT.OnErrorEvent) {
        this.http.post('youtube/log-client-error', {code: e.data, videoUrl: this.youtube.getVideoUrl()})
            .subscribe();

        // if we had a youtube_id set on track and it was invalid, search for valid videos to play
        if (this.activeTrack?.youtube_id && this.searchResults === null) {
            await this.searchYoutubeForVideoMatches();
        }

        // try to play alternative videos we fetched
        if (this.searchResults?.length) {
            this.assignFirstSearchResult();
            this.cueYoutubeVideo(this.activeTrack);
            this.youtube.playVideo();

        // there are no more alternative videos to try, we can error out
        } else {
            this.activeTrack = null;
            this.searchResults = null;
            this.setState('playing', false);
            this.tracksSkippedDueToError++;

            // try to play up to two next queued tracks if we can't play
            // a video for this one. If we can't play 3 tracks in a row
            // we can assume there's an issue with youtube API and bail
            if (this.tracksSkippedDueToError <= 2) {
                this.state.firePlaybackEnded();
            }
        }
    }

    private onYoutubeStateChange(e: YT.OnStateChangeEvent) {
        switch (e.data) {
            case YT.PlayerState.ENDED:
                this.state.firePlaybackEnded();
                this.setState('playing', false);
                break;
            case YT.PlayerState.PLAYING:
                this.tracksSkippedDueToError = 0;
                this.setState('playing', true);
                break;
            case YT.PlayerState.PAUSED:
                this.setState('playing', false);
                break;
        }
    }

    private getPlayerVars(): YT.PlayerVars {
        return {
            autoplay: 0,
            rel: 0,
            showinfo: 0,
            disablekb: 1,
            controls: 0,
            modestbranding: 1,
            iv_load_policy: 3,
            playsinline: 1,
        };
    }

    private setState(name: string, value: boolean) {
        this.zone.run(() => this.state[name] = value);
    }

    public maximize() {
        requestFullScreen(this.youtube?.getIframe());
    }
}
