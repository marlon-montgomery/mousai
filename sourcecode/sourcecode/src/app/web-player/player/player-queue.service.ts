import {Injectable} from '@angular/core';
import {Track} from '../../models/Track';
import {PlayerState} from './player-state.service';
import {shuffler} from './shuffler';
import {LocalStorage} from '@common/core/services/local-storage.service';
import {Settings} from '@common/core/config/settings.service';
import {BehaviorSubject} from 'rxjs';

@Injectable({
    providedIn: 'root'
})
export class PlayerQueue {
    public shuffledQueue$ = new BehaviorSubject<Track[]>([]);
    private originalQueue: Track[] = [];
    private pointer = 0;

    /**
     * Id of item that is currently queued, if any.
     * (album, artist, playlist etc)
     */
    public queuedMediaItemId: string;

    constructor(
        private state: PlayerState,
        private localStorage: LocalStorage,
    ) {}

    public getAll(): Track[] {
        return this.shuffledQueue$.value;
    }

    /**
     * Check if specified track is currently in queue.
     */
    public has(track: Track): boolean {
        return this.getAll().findIndex(predicate => {
            return predicate.id === track.id;
        }) >= 0;
    }

    /**
     * Move queue pointer to specified track.
     */
    public select(track: Track) {
       if (track) {
           this.set(this.getAll().findIndex(predicate => {
               return predicate.id === track.id;
           }));
           this.updateMediaSessionMetadata();
       }
    }

    public empty() {
        return this.getTotal() > 0;
    }

    public override(params: {tracks: Track[], queuedItemId?: string}, queuePointer: number = 0) {
        this.queuedMediaItemId = params.queuedItemId;
        this.shuffledQueue$.next([...params.tracks]);
        if (this.state.shuffling) this.shuffle(true);
        this.originalQueue = params.tracks.slice();
        this.set(queuePointer);
    }

    public append(tracks: Track[]) {
        const queue = this.shuffledQueue$.value.concat(tracks.slice());
        this.shuffledQueue$.next(queue);
        this.originalQueue = [...queue];
    }

    public prepend(tracks: Track[]) {
        this.shuffledQueue$.next(this.addTracksAtIndex(this.shuffledQueue$.value, tracks));
        this.originalQueue = this.addTracksAtIndex(this.originalQueue, tracks);
    }

    private addTracksAtIndex(array: Track[], tracksToAdd: Track[]) {
        const tail = array.splice(this.pointer + 1);
        return [...array, ...tracksToAdd, ...tail];
    }

    public remove(track: Track) {
        let i = this.getAll().findIndex(predicate => predicate === track);
        if (i === -1) i = this.getAll().findIndex(predicate => predicate.id === track.id);
        const queue = [...this.shuffledQueue$.value];
        queue.splice(i, 1);
        this.shuffledQueue$.next(queue);
    }

    public shuffle(keepFirst = false) {
        this.shuffledQueue$.next(shuffler.shuffle(this.getAll(), keepFirst));
    }

    public mediaItemIsQueued(itemId: string) {
        return this.queuedMediaItemId === itemId;
    }

    /** Restore queue to original (if it has been shuffled) */
    public restoreOriginal() {
        this.shuffledQueue$.next(this.originalQueue.slice());
    }

    public getFirst(): Track {
        return this.get(0);
    }

    public getLast(): Track {
        return this.get(this.getTotal() - 1);
    }

    public getTotal() {
        return this.getAll().length;
    }

    public getNext(): Track {
        return this.get(this.pointer + 1);
    }

    /**
     * Check if current track is the last one in queue.
     */
    public isLast() {
        return this.getLast() === this.getCurrent();
    }

    /**
     * Check if current track is the first one in queue.
     */
    public isFirst() {
        return this.getFirst() === this.getCurrent();
    }

    public getPrevious(): Track {
        return this.get(this.pointer - 1);
    }

    public getCurrent() {
        return this.get(this.pointer);
    }

    public get(index: number): Track {
        return this.shuffledQueue$.value[index] || null;
    }

    public set(index: number) {
        if (index === -1) index = null;
        this.pointer = index;
        this.localStorage.set('player.queue.pointer', index);
    }

    private updateMediaSessionMetadata() {
        const track = this.getCurrent();
        if ('mediaSession' in navigator && track?.id && this.state.activePlaybackStrategy === 'html5') {
            const image = track.image || track?.album?.image;
            const metadata = new MediaMetadata({
                title: track.name,
                album: track?.album?.name,
            });
            if (track.artists?.length) {
                metadata.artist = track.artists[0].name;
            }
            if (image) {
                metadata.artwork = [
                    {src: image , sizes: '300x300',   type: 'image/jpg'},
                ];
            }
            navigator.mediaSession.metadata = metadata;
        }
    }
}
