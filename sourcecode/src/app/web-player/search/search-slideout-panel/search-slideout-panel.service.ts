import {catchError, debounceTime, distinctUntilChanged, switchMap} from 'rxjs/operators';
import {Injectable} from '@angular/core';
import {FormControl} from '@angular/forms';
import {Search} from '../search.service';
import {of as observableOf} from 'rxjs';
import {Router} from '@angular/router';
import {SearchResults} from '../search-results';
import {MAIN_SEARCH_MODELS} from '../../../models/model_types';

@Injectable({
    providedIn: 'root'
})
export class SearchSlideoutPanel {
    public noResults = false;
    public searching = false;
    public isOpen = false;
    public searchQuery = new FormControl();
    public results: SearchResults;

    constructor(private search: Search, private router: Router) {
        this.bindToSearchQuery();
        this.results = this.getEmptyResultSet();
    }

    public close() {
        this.searchQuery.reset();
        this.isOpen = false;
        this.results = this.getEmptyResultSet();
    }

    public open() {
        this.isOpen = true;
    }

    public clearInput() {
        this.searchQuery.reset();
    }

    public goToSearchPage() {
        if ( ! this.searchQuery.value) return;
        this.router.navigate(['/search', this.searchQuery.value]);
    }

    private bindToSearchQuery() {
        this.searchQuery.valueChanges.pipe(
            debounceTime(400),
            distinctUntilChanged(),
            switchMap(query => {
                this.searching = true;
                if ( ! query) return observableOf({results: this.getEmptyResultSet()});
                return this.search.media(query, {limit: 3, types: MAIN_SEARCH_MODELS}).pipe(catchError(() => {
                    this.searching = false;
                    return observableOf({results: this.getEmptyResultSet()});
                }));
            })).subscribe(response => {
                this.results = response.results;
                this.noResults = !this.responseHasResults(response);
                this.searching = false;
                if (this.searchQuery.value) this.open();
            });
    }

    private responseHasResults(response: Object): boolean {
        for (const key in response) {
            if (response[key].length) return true;
        }
    }

    private getEmptyResultSet(): SearchResults {
        return {
            albums: [],
            artists: [],
            tracks: [],
            playlists: [],
            users: [],
        };
    }
}
