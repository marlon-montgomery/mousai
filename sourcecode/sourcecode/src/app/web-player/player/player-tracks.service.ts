import {Injectable} from '@angular/core';
import {AppHttpClient} from '@common/core/http/app-http-client.service';
import {Track} from '../../models/Track';
import {Subscription} from 'rxjs';

@Injectable({
    providedIn: 'root'
})
export class PlayerTracksService {
    private loadSub: Subscription;

    constructor(private http: AppHttpClient) {}

    public load(queueId: string, lastTrack?: Track): Promise<Track[]> {
        // prevent multiple loads at the same time.
        if (this.loadSub) {
            this.loadSub.unsubscribe();
            this.loadSub = null;
        }
        return new Promise(resolve => {
            this.loadSub = this.http.post<{tracks: Track[]}>('player/tracks', {queueId, lastTrack})
                .subscribe(response => {
                    resolve(response.tracks);
                }, () => resolve([]));
        });
    }
}
