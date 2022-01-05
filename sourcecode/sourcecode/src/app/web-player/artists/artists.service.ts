import {Injectable} from '@angular/core';
import {Artist} from '../../models/Artist';
import {PaginationResponse} from '@common/core/types/pagination/pagination-response';
import {Track} from '../../models/Track';
import {Album} from '../../models/Album';
import {BackendResponse} from '@common/core/types/backend-response';
import {PaginatedBackendResponse} from '@common/core/types/pagination/paginated-backend-response';
import {HttpCacheClient} from '@common/core/http/http-cache-client';
import {User} from '@common/core/types/models/User';

export interface GetArtistResponse {
    artist: Artist;
    albums: PaginationResponse<Album>;
    top_tracks?: Track[];
}

@Injectable({
    providedIn: 'root'
})
export class Artists {
    constructor(private http: HttpCacheClient) {}

    public get(id: number, params = {}): BackendResponse<GetArtistResponse> {
        return this.http.get(`artists/${id}`, params);
    }

    public create(payload: object): BackendResponse<{artist: Artist}> {
        return this.http.post('artists', payload);
    }

    public update(id: number, payload: object): BackendResponse<{artist: Artist}> {
        return this.http.put('artists/' + id, payload);
    }

    public paginateTracks(id: number, page = 1): PaginatedBackendResponse<Track> {
        return this.http.getWithCache(`artists/${id}/tracks`, {page});
    }

    public paginateAlbums(id: number, page = 1): PaginatedBackendResponse<Album> {
        return this.http.getWithCache(`artists/${id}/albums`, {page});
    }

    public paginateFollowers(id: number, page = 1): PaginatedBackendResponse<User> {
        return this.http.getWithCache(`artists/${id}/followers`, {page});
    }

    public delete(ids: number[]) {
        return this.http.delete('artists', {ids});
    }
}
