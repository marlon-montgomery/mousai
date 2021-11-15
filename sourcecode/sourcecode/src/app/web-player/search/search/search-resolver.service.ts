import {Injectable} from '@angular/core';
import {Router, Resolve, RouterStateSnapshot, ActivatedRouteSnapshot} from '@angular/router';
import {Search} from '../search.service';
import {WebPlayerState} from '../../web-player-state.service';
import {SearchResponse, SearchResults} from '../search-results';
import {MAIN_SEARCH_MODELS} from '../../../models/model_types';

@Injectable({
    providedIn: 'root'
})
export class SearchResolver implements Resolve<SearchResults> {
    private lastSearch: SearchResponse = {query: '', results: {}};

    constructor(
        private search: Search,
        private router: Router,
        private state: WebPlayerState
    ) {}

    resolve(route: ActivatedRouteSnapshot, state: RouterStateSnapshot): Promise<SearchResults> {
        this.state.loading = true;

        const query = route.paramMap.get('query');

        if (this.lastSearch.query === query) {
            this.state.loading = false;
            return new Promise(resolve => resolve(this.lastSearch.results)) as any;
        }

        return this.search.media(query, {limit: 20, types: MAIN_SEARCH_MODELS}).toPromise().then(response => {
            this.state.loading = false;

            if (response.results) {
                this.lastSearch = response;
                return response.results;
            } else {
                this.router.navigate(['/']);
                return null;
            }
        }).catch(() => {
            this.state.loading = false;
            this.router.navigate(['/']);
        }) as any;
    }
}
