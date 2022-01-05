import {
    ChangeDetectionStrategy,
    Component,
    ElementRef, Input,
    OnInit,
    ViewChild
} from '@angular/core';
import {ControlValueAccessor, FormControl, NG_VALUE_ACCESSOR} from '@angular/forms';
import {BehaviorSubject, Observable, of} from 'rxjs';
import {
    catchError,
    debounceTime,
    distinctUntilChanged,
    finalize,
    map,
    switchMap
} from 'rxjs/operators';
import {Search} from '../../web-player/search/search.service';
import {Artist} from '../../models/Artist';

@Component({
    selector: 'select-artist-control',
    templateUrl: './select-artist-control.component.html',
    styleUrls: ['./select-artist-control.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush,
    host: {'tabindex': '0'},
    providers: [{
        provide: NG_VALUE_ACCESSOR,
        useExisting: SelectArtistControlComponent,
        multi: true,
    }]
})
export class SelectArtistControlComponent implements OnInit, ControlValueAccessor {
    @ViewChild('searchInput') searchInput: ElementRef<HTMLInputElement>;
    @ViewChild('fakeInput') fakeInput: ElementRef<HTMLDivElement>;
    @Input() listAll = false;
    public isDisabled$ = new BehaviorSubject<boolean>(false);
    public searchFormControl = new FormControl();
    public loading$ = new BehaviorSubject(false);
    public artists$ = new BehaviorSubject<Artist[]>([]);
    public selectedArtist$ = new BehaviorSubject<Artist>(null);
    private propagateChange: (artist: Artist) => void;
    public searchedOnce = false;

    constructor(private search: Search) {}

    ngOnInit() {
        this.bindToSearchControl();
    }

    public writeValue(value: Artist) {
        this.selectedArtist$.next(value);
    }

    public registerOnChange(fn: (artist: Artist) => void) {
        this.propagateChange = fn;
    }

    public registerOnTouched() {
    }

    public setDisabledState(isDisabled: boolean) {
        this.isDisabled$.next(isDisabled);
    }

    private bindToSearchControl() {
        this.searchFormControl.valueChanges.pipe(
            debounceTime(150),
            distinctUntilChanged(),
            switchMap(query => this.searchArtists(query)),
            catchError(() => of([])),
        ).subscribe(users => {
            this.searchedOnce = true;
            this.artists$.next(users);
        });
    }

    private searchArtists(query: string): Observable<Artist[]> {
        this.loading$.next(true);
        return this.search.suggestArtists({
            query,
            limit: 7,
            listAll: this.listAll,
        }).pipe(
            finalize(() => this.loading$.next(false)),
            map(response => response.artists),
        );
    }

    public onMenuOpened() {
        const menu = (document.querySelector('.select-artist-control-menu') as HTMLElement);
        menu.style.width = this.fakeInput.nativeElement.getBoundingClientRect().width + 'px';

        if (!this.searchedOnce) {
            this.clearSearchInput();
        }
    }

    public selectArtist(artist: Artist) {
        this.selectedArtist$.next(artist);
        this.propagateChange(artist);
    }

    public clearSearchInput() {
        this.searchFormControl.setValue('');
    }

    public onMenuClosed() {
        this.loading$.next(false);
        this.clearSearchInput();
    }
}
