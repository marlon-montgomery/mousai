import {Injectable} from '@angular/core';
import {AppHttpClient} from '@common/core/http/app-http-client.service';
import {Genre} from '../../models/Genre';
import {BackendResponse} from '@common/core/types/backend-response';
import {Channel} from '../../admin/channels/channel';

@Injectable({
    providedIn: 'root'
})
export class Genres {
    constructor(private httpClient: AppHttpClient) {}

    public create(params: Partial<Genre>): BackendResponse<{genre: Genre}> {
        return this.httpClient.post('genres', params);
    }

    public update(id: number, params: Partial<Genre>): BackendResponse<{genre: Genre}> {
        return this.httpClient.put('genres/' + id, params);
    }

    public delete(ids: number[]): BackendResponse<void> {
        return this.httpClient.delete('genres', {ids});
    }

    public get(name: string, params = {}): BackendResponse<{genre: Genre, channel: Channel}> {
        return this.httpClient.get(`genres/${name}`, params);
    }
}
