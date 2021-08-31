import {Injectable} from '@angular/core';
import {AppHttpClient} from '@common/core/http/app-http-client.service';
import {Album} from '../../models/Album';
import {BackendResponse} from '@common/core/types/backend-response';
import {Subject} from 'rxjs';
import {tap} from 'rxjs/operators';

@Injectable({
    providedIn: 'root'
})
export class Albums {
    public albumsDeleted$ = new Subject<number[]>();
    constructor(private httpClient: AppHttpClient) {}

    public get(id: number, params?: object): BackendResponse<{album: Album}> {
        return this.httpClient.get('albums/' + id, params);
    }

    public create(payload: Album): BackendResponse<{album: Album}> {
        return this.httpClient.post('albums', payload);
    }

    public update(id: number, payload: object): BackendResponse<{album: Album}> {
        return this.httpClient.put('albums/' + id, payload);
    }

    public delete(ids: number[]) {
        return this.httpClient.delete('albums', {ids})
            .pipe(tap(() => this.albumsDeleted$.next(ids)));
    }
}
