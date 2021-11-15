import {
    AfterViewInit,
    ChangeDetectionStrategy,
    ChangeDetectorRef,
    Component,
    ElementRef,
    EventEmitter,
    HostListener,
    Input,
    OnDestroy,
    OnInit,
    Output,
    QueryList,
    ViewChild,
    ViewChildren,
} from '@angular/core';
import {FormBuilder, FormControl} from '@angular/forms';
import {ActivatedRoute, Router} from '@angular/router';
import {distinctUntilChanged, filter, map, startWith} from 'rxjs/operators';
import {
    BehaviorSubject,
    combineLatest,
    fromEvent,
    merge,
    Observable,
    Subscription,
} from 'rxjs';
import {
    DatatableFilter,
    FilterValue,
} from '@common/datatable/datatable-filters/search-input-with-filters/filter-config/datatable-filter';
import {Keybinds} from '@common/core/keybinds/keybinds.service';
import {FilterSuggestionsComponent} from './filter-suggestions/filter-suggestions.component';
import {ActiveFiltersComponent} from './active-filters/active-filters.component';

interface FormData {
    [key: string]: {
        key: string;
        value: {value: FilterValue; operator: string} | FilterValue;
        operator: string;
    };
}

@Component({
    selector: 'search-input-with-filters',
    templateUrl: './search-input-with-filters.component.html',
    styleUrls: ['./search-input-with-filters.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class SearchInputWithFiltersComponent implements OnInit, AfterViewInit, OnDestroy {
    @Input() compact: boolean;
    @Input() searchControl: FormControl;
    @Input() pluralName: string;
    @Input() set filters(filters: DatatableFilter[]) {
        if (filters) {
            this.keyedFilters = {};
            (filters || []).forEach(filter => {
                this.keyedFilters[filter.key] = filter;
            });
        } else {
            this.keyedFilters = null;
        }
    }

    @Output() filterChange = new EventEmitter<string>();
    @Output() inputFocus = new EventEmitter();

    @ViewChild('searchInput') searchInput: ElementRef<HTMLInputElement>;
    @ViewChild(FilterSuggestionsComponent) suggestions: FilterSuggestionsComponent;
    @ViewChild(ActiveFiltersComponent) activeFilters: ActiveFiltersComponent;
    @ViewChildren('filterSuggestion') filterContainers: QueryList<
        ElementRef<HTMLElement>
    >;

    keyedFilters: {[key: string]: DatatableFilter};

    form = this.fb.group({});

    filterBarHeight = 0;
    filterDropdownVisible$ = new BehaviorSubject<boolean>(false);
    private closeActionsStream$: Subscription;

    haveFiltersOrQuery$: Observable<boolean>;

    constructor(
        private fb: FormBuilder,
        private route: ActivatedRoute,
        private el: ElementRef<HTMLElement>,
        private keybinds: Keybinds,
        private cd: ChangeDetectorRef,
        private router: Router
    ) {}

    ngOnInit() {
        this.haveFiltersOrQuery$ = combineLatest([
            this.searchControl.valueChanges.pipe(startWith(null)),
            this.form.valueChanges.pipe(startWith(null)),
        ]).pipe(map(([s, f]) => s || (f && Object.keys(f).length !== 0)));
    }

    ngAfterViewInit() {
        this.watchForSizeChanges();
        this.setFiltersFromQuery();
        this.setupKeybinds();
        this.subscribeToCloseActions();
        this.bindToFormValueChange();
    }

    @HostListener('click', ['$event'])
    onClick(e: MouseEvent) {
        if (
            e.target === this.el.nativeElement ||
            e.target === this.activeFilters.el.nativeElement
        ) {
            this.searchInput.nativeElement.focus();
        }
    }

    ngOnDestroy() {
        this.closeActionsStream$?.unsubscribe();
    }

    clearSearch() {
        Object.keys(this.form.controls).forEach(key => {
            this.activeFilters.removeByKey(key);
        });
        // prevent double datatable reload
        this.searchControl.reset(null, {emitEvent: false});
        this.form.reset();
    }

    toggleFilterDropdown() {
        if (this.filterDropdownVisible$.value) {
            this.filterDropdownVisible$.next(false);
        } else {
            this.filterDropdownVisible$.next(true);
        }
    }

    addFilterFromDropdownClick(config: DatatableFilter) {
        this.activeFilters.add(config, {focus: true});
        this.filterDropdownVisible$.next(false);
    }

    private searchInputIsFocused(): boolean {
        return document.activeElement === this.searchInput.nativeElement;
    }

    private cursorAtStartOfSearchInput(): boolean {
        return (
            this.searchInputIsFocused() &&
            this.searchInput.nativeElement.selectionStart === 0
        );
    }

    private cursorAtEndOfSearchInput(): boolean {
        return (
            this.searchInputIsFocused() &&
            this.searchInput.nativeElement.selectionStart ===
                this.searchInput.nativeElement.value.length
        );
    }

    private bindToFormValueChange() {
        this.form.valueChanges
            .pipe(
                map(v => (Object.keys(v).length ? v : null)),
                distinctUntilChanged()
            )
            .subscribe((formData: FormData) => {
                this.filterChange.emit(formData ? encodeFilterFormData(formData) : null);
            });
    }

    private watchForSizeChanges() {
        const resizeObserver = new ResizeObserver(entries => {
            this.filterBarHeight = entries[0].contentRect.height;
        });
        resizeObserver.observe(this.el.nativeElement, {box: 'border-box'});
    }

    private setFiltersFromQuery() {
        const qp = this.router.routerState.root.snapshot.queryParams;
        if (qp.filters) {
            const filterValues = decodeFilterString(qp.filters);
            filterValues.forEach(filterValue => {
                this.activeFilters.add(this.keyedFilters[filterValue.key], filterValue);
            });
        }
        if (qp.query) {
            this.searchControl.setValue(qp.query);
        }
    }

    private setupKeybinds() {
        this.keybinds.add(['backspace', 'delete'], () => {
            if (this.cursorAtStartOfSearchInput()) {
                return this.activeFilters.focusLast();
            }
            if (this.activeFilters.anyFocused()) {
                this.activeFilters.removeCurrentlyFocused();
                return this.searchInput.nativeElement.focus();
            }
        });

        this.keybinds.add('arrow_left', e => {
            if (this.cursorAtStartOfSearchInput()) {
                this.activeFilters.focusLast();
                e.preventDefault();
            } else if (this.activeFilters.anyFocused()) {
                this.activeFilters.focusPrevious();
                e.preventDefault();
            }
        });

        this.keybinds.add('arrow_right', e => {
            if (this.activeFilters.anyFocused()) {
                e.preventDefault();
                const i = this.activeFilters.getFocusedIndex();
                const next = this.activeFilters.getByIndex(i + 1);
                if (next) {
                    next.focus();
                } else if (this.activeFilters.lastIsFocused()) {
                    this.searchInput.nativeElement.focus();
                }
            }
        });

        this.keybinds.add(['arrow_down', 'tab'], e => {
            if (this.cursorAtEndOfSearchInput() || this.suggestions.anyFocused()) {
                e.preventDefault();
                if (!this.filterDropdownVisible$.value) {
                    this.toggleFilterDropdown();
                }
                const currentIndex = this.suggestions.getFocusedIndex();
                let newIndex = currentIndex > -1 ? currentIndex : 0;
                if (this.suggestions.anyFocused()) {
                    newIndex += 1;
                }
                if (newIndex >= this.suggestions.items.length) {
                    newIndex = 0;
                }
                this.suggestions.focusItemAt(newIndex);
            }
        });

        this.keybinds.add(['arrow_up', 'shift+tab'], e => {
            if (this.suggestions.anyFocused()) {
                e.preventDefault();
                const currentIndex = this.activeFilters.getFocusedIndex();
                let newIndex = currentIndex > -1 ? currentIndex : 0;
                newIndex -= 1;
                if (newIndex === -1) {
                    this.searchInput.nativeElement.focus();
                } else {
                    this.suggestions.focusItemAt(newIndex);
                }
            }
        });

        this.keybinds.add('enter', e => {
            const focused = this.suggestions.getFocusedFilter();
            if (focused) {
                e.preventDefault();
                this.activeFilters.add(focused, {focus: true});
                this.filterDropdownVisible$.next(false);
            }
        });

        this.keybinds.add('esc', e => {
            if (this.filterDropdownVisible$.value) {
                e.preventDefault();
                this.filterDropdownVisible$.next(false);
            }
        });

        this.keybinds.listenOn(document, {
            fireIfInputFocused: true,
        });
    }

    private subscribeToCloseActions() {
        this.closeActionsStream$ = merge(
            fromEvent(document, 'click') as Observable<MouseEvent>,
            fromEvent(document, 'auxclick') as Observable<MouseEvent>,
            fromEvent(document, 'touchend') as Observable<TouchEvent>
        )
            .pipe(
                filter(event => {
                    const clickTarget = event.target as HTMLElement;
                    return (
                        clickTarget !== this.el.nativeElement &&
                        !this.el.nativeElement.contains(clickTarget)
                    );
                })
            )
            .subscribe(() => {
                this.filterDropdownVisible$.next(false);
            });
    }
}

const encodeFilterFormData = (formData: FormData): string => {
    const filters = [];
    Object.values(formData).forEach(filter => {
        if (filter.value !== '') {
            filters.push(filter);
        }
    });
    if (!filters.length) {
        return '';
    }
    return encodeURIComponent(btoa(JSON.stringify(filters)));
};

const decodeFilterString = (filterString: string): DecodedValue[] => {
    let filtersFromQuery = [];
    try {
        filtersFromQuery = JSON.parse(atob(decodeURIComponent(filterString)));
    } catch (e) {
        //
    }
    return filtersFromQuery;
};

interface DecodedValue {
    key: string;
    value: {value: FilterValue; operator: string};
    operator: string;
}
