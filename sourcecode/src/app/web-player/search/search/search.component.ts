import {debounceTime, distinctUntilChanged, switchMap} from 'rxjs/operators';
import {AfterViewInit, Component, ElementRef, OnInit, ViewChild, ViewEncapsulation} from '@angular/core';
import {ActivatedRoute} from '@angular/router';
import {WebPlayerUrls} from '../../web-player-urls.service';
import {FormControl} from '@angular/forms';
import {of as observableOf} from 'rxjs';
import {Search} from '../search.service';
import {WebPlayerState} from '../../web-player-state.service';
import {Settings} from '@common/core/config/settings.service';
import {SearchResults} from '../search-results';
import {MAIN_SEARCH_MODELS} from '../../../models/model_types';

@Component({
    selector: 'search',
    templateUrl: './search.component.html',
    styleUrls: ['./search.component.scss'],
    encapsulation: ViewEncapsulation.None
})
export class SearchComponent implements OnInit, AfterViewInit {
    @ViewChild('searchInput') searchInput: ElementRef;

    public searchTerm: string;

    /**
     * Whether search is currently in progress.
     */
    public searching = false;

    /**
     * Whether search was already performed at least once.
     */
    public searchedOnce = false;

    /**
     * Currently active search page tab.
     */
    private activeTab = 'topResults';

    public searchControl = new FormControl();
    public results: SearchResults;

    constructor(
        private route: ActivatedRoute,
        public urls: WebPlayerUrls,
        private search: Search,
        public state: WebPlayerState,
        public settings: Settings
    ) {}

    ngOnInit() {
        this.route.data.subscribe(data => {
            this.results = data.results;
        });

        this.route.params.subscribe(params => {
            this.searchTerm = params.query;
            this.searchControl.setValue(this.searchTerm, {emitEvent: false});
            this.activeTab = params.tab || 'topResults';
            if (this.searchTerm) this.searchedOnce = true;
        });

        this.bindToSearchQuery();
    }

    ngAfterViewInit() {
        if (this.searchInput) {
            this.searchInput.nativeElement.focus();
        }
    }

    public activeTabIs(name: string) {
        return this.activeTab === name;
    }

    /**
     * Get router link for specified search tab.
     */
    public getRouterLink(tab?: string) {
        return this.urls.search(this.searchControl.value, tab);
    }

    /**
     * Check if any results have been found.
     */
    public hasResults(): boolean {
        if ( ! this.results || ! Object.keys(this.results)) return false;

        for (const type in this.results) {
            if (this.results[type].length) return true;
        }
    }

    /**
     * Check if there's a search term.
     */
    public hasSearchTerm() {
        return this.searchTerm || this.searchControl.value;
    }

    /**
     * Clear current search and input.
     */
    public clearSearch() {
        this.searchControl.setValue(null);
        this.searchTerm = null;
    }

    /**
     * Perform a search when user types into search input.
     */
    private bindToSearchQuery() {
        this.searchControl.valueChanges.pipe(
            debounceTime(400),
            distinctUntilChanged(),
            switchMap(query => {
                this.searching = true;
                if ( ! query) return observableOf({results: this.getEmptyResults()});
                return this.search.media(query, {limit: 20, types: MAIN_SEARCH_MODELS});
            })
        ).subscribe(response => {
            this.results = response.results || this.getEmptyResults();
            this.searching = false;
            this.searchedOnce = true;
        }, () => {
            return observableOf(this.getEmptyResults());
        });
    }

    /**
     * Get empty search results set.
     */
    private getEmptyResults(): SearchResults {
        return {
            artists: [],
            albums: [],
            tracks: [],
            playlists: [],
            users: [],
        };
    }

    public blurSearchInput() {
        this.searchInput.nativeElement.blur();
    }
}
