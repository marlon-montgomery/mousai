import {ChangeDetectionStrategy, Component, ElementRef, OnInit, ViewChild} from '@angular/core';
import {catchError, debounceTime, distinctUntilChanged, finalize, map, switchMap} from 'rxjs/operators';
import {ControlValueAccessor, FormControl, NG_VALUE_ACCESSOR} from '@angular/forms';
import {BehaviorSubject, Observable, of} from 'rxjs';
import {Album, ALBUM_MODEL} from '../../../models/Album';
import {Search} from '../../../web-player/search/search.service';
import {CurrentUser} from '@common/auth/current-user';

@Component({
    selector: 'album-control',
    templateUrl: './album-control.component.html',
    styleUrls: ['./album-control.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush,
    providers: [{
        provide: NG_VALUE_ACCESSOR,
        useExisting: AlbumControlComponent,
        multi: true,
    }]
})
export class AlbumControlComponent implements ControlValueAccessor, OnInit {
    @ViewChild('searchInput', {static: true}) searchInput: ElementRef<HTMLInputElement>;
    @ViewChild('fakeInput') fakeInput: ElementRef<HTMLDivElement>;
    public searchFormControl = new FormControl();
    public loading$ = new BehaviorSubject(false);
    public results$ = new BehaviorSubject<Album[]>([]);
    public selectedAlbum$ = new BehaviorSubject<Album>(null);
    private propagateChange: (album: Album) => void;
    public searchedOnce = false;

    constructor(
        private search: Search,
        public currentUser: CurrentUser,
    ) {}

    ngOnInit() {
        this.bindToSearchControl();
    }

    public writeValue(value: Album) {
        this.selectedAlbum$.next(value);
    }

    public registerOnChange(fn: (album: Album) => void) {
        this.propagateChange = fn;
    }

    public registerOnTouched() {}

    private bindToSearchControl() {
        this.searchFormControl.valueChanges.pipe(
            debounceTime(150),
            distinctUntilChanged(),
            switchMap(query => this.searchAlbums(query)),
            catchError(() => of([])),
        ).subscribe(albums => {
            this.searchedOnce = true;
            this.results$.next(albums);
        });
    }

    private searchAlbums(query: string): Observable<Album[]> {
        this.loading$.next(true);
        return this.search.suggestAlbums({query, limit: 7})
            .pipe(
                finalize(() =>  this.loading$.next(false)),
                map(response => response.albums),
            );
    }

    public onMenuOpened() {
        const menu = (document.querySelector('.add-album-control-menu') as HTMLElement);
        menu.style.width = this.fakeInput.nativeElement.getBoundingClientRect().width + 'px';

        if (!this.searchedOnce) {
            this.clearSearchInput();
        }
    }

    public selectAlbum(album: Album) {
        this.selectedAlbum$.next(album);
        this.propagateChange(album);
    }

    public clearSearchInput() {
        this.searchFormControl.setValue('');
    }

    public onMenuClosed() {
        this.loading$.next(false);
        this.clearSearchInput();
    }
}
