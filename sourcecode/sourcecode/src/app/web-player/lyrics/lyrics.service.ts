import {Injectable} from '@angular/core';
import {AppHttpClient} from '@common/core/http/app-http-client.service';
import {Lyric} from '../../models/Lyric';
import {BackendResponse} from '@common/core/types/backend-response';

@Injectable({
    providedIn: 'root'
})
export class Lyrics {
    constructor(private http: AppHttpClient) {}

    public get(trackId: number): BackendResponse<{lyric: Lyric}> {
        return this.http.get(`tracks/${trackId}/lyrics`);
    }

    public create(payload: object): BackendResponse<{lyric: Lyric}> {
        return this.http.post('lyrics', payload);
    }

    public update(id: number, payload: object): BackendResponse<{lyric: Lyric}> {
        return this.http.put('lyrics/' + id, payload);
    }
    public delete(ids: number[]): BackendResponse<void> {
        return this.http.delete('lyrics', {ids});
    }
}
