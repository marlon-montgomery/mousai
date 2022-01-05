import {Injectable} from '@angular/core';
import {AppHttpClient} from '@common/core/http/app-http-client.service';
import {Observable} from 'rxjs';
import {SearchResponse} from './search-results';
import {BackendResponse} from '@common/core/types/backend-response';
import {Track} from '../../models/Track';
import {Artist} from '../../models/Artist';
import {Album} from '../../models/Album';

interface SearchEverythingParams {
    query?: string;
    limit?: number;
    flatten?: boolean;
    types: string[];
    localOnly?: boolean;
}

@Injectable({
    providedIn: 'root'
})
export class Search {
    constructor(private http: AppHttpClient) {}

    public media(query: string = '', params: SearchEverythingParams): BackendResponse<SearchResponse> {
        params.query = query;
        return this.http.get('search', params);
    }

    public videoId(artistName: string, track: Track): Observable<{ title: string, id: string }[]> {
        return this.http.get(`search/audio/${track.id}/${this.doubleEncode(artistName)}/${this.doubleEncode(track.name)}`);
    }

    public suggestArtists(params: {query: string, limit?: number, listAll?: boolean}): BackendResponse<{artists: Artist[]}> {
        return this.http.get('search/suggestions/artists', params);
    }

    public suggestAlbums(params: {query: string, limit?: number}): BackendResponse<{albums: Album[]}> {
        return this.http.get('search/suggestions/albums', params);
    }

    private doubleEncode(value: string) {
        return encodeURIComponent(encodeURIComponent(value));
    }
}
