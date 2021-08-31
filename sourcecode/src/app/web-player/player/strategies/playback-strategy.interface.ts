import {Track} from '../../../models/Track';

export interface PlaybackStrategy {

    /**
     * Play video.
     */
    play();

    /**
     * Pause video.
     */
    pause();

    /**
     * stop video.
     */
    stop();

    /**
     * Seek to specified time in video.
     */
    seekTo(time: number);

    /**
     * Get loaded video duration in seconds.
     */
    getDuration();

    /**
     * Get elapsed time in seconds since the video started playing
     */
    getCurrentTime();

    /**
     * Set player volume.
     */
    setVolume(volume: number);

    /**
     * Mute player.
     */
    mute();

    /**
     * Unmute player.
     */
    unMute();

    /**
     * Get track that is currently cued for playback.
     */
    getCuedTrack();

    /**
     * Check if player is ready.
     */
    isReady();

    /**
     * Cue specified track.
     */
    cueTrack(track: Track): Promise<any>;

    maximize();

    /**
     * Destroy playback strategy.
     */
    destroy();
}
