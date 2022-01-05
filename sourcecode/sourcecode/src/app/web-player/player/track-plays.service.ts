import {Injectable} from '@angular/core';
import {Track} from '../../models/Track';
import {Tracks} from '../tracks/tracks.service';

@Injectable({
    providedIn: 'root'
})
export class TrackPlays {
    private loggedTracks: number[] = [];
    constructor(private tracks: Tracks) {}

    public increment(track: Track, queueId: string) {
        if ( ! track || this.hasBeenPlayed(track)) return;
        this.loggedTracks.push(track.id);
        this.tracks.logPlay(track, {queueId}).subscribe(() => {}, () => {});
    }

    /**
     * Clear last track, if it matches specified track.
     * This will allow this track plays to be incremented again.
     */
    public clearPlayedTrack(track: Track) {
        if ( ! track) return;
        this.loggedTracks.splice(this.loggedTracks.indexOf(track.id), 1);
    }

    /**
     * Check if current user has already incremented plays of specified track.
     */
    private hasBeenPlayed(track: Track): boolean {
        return this.loggedTracks.indexOf(track.id) > -1;
    }
}
