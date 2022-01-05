import {
    AfterViewInit,
    ChangeDetectionStrategy,
    Component,
    ElementRef,
    Input,
    OnInit,
    ViewChild
} from '@angular/core';
import {ControlValueAccessor, FormControl, NG_VALUE_ACCESSOR} from '@angular/forms';
import {BehaviorSubject, fromEvent, of as observableOf} from 'rxjs';
import {UploadQueueService} from '@common/uploads/upload-queue/upload-queue.service';
import {debounceTime, distinctUntilChanged, take} from 'rxjs/operators';
import {Search} from '../../../web-player/search/search.service';
import {Artist} from '../../../models/Artist';
import {CurrentUser} from '@common/auth/current-user';

@Component({
    selector: 'track-artist-control',
    templateUrl: './track-artist-control.component.html',
    styleUrls: ['./track-artist-control.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush,
    host: {'class': 'custom-control'},
    providers: [UploadQueueService, {
        provide: NG_VALUE_ACCESSOR,
        useExisting: TrackArtistControlComponent,
        multi: true,
    }]
})
export class TrackArtistControlComponent implements OnInit, AfterViewInit, ControlValueAccessor {
    @ViewChild('searchInput') searchInput: ElementRef<HTMLInputElement>;
    @Input() error: string;
    @Input() id: string;

    public searchControl = new FormControl();
    public value$ = new BehaviorSubject<Artist[]>([]);
    public searchResults$ = new BehaviorSubject<Artist[]>([]);
    private propagateChange: (artists: Artist[]) => void;

    constructor(
        private search: Search,
        public currentUser: CurrentUser,
    ) {}

    ngOnInit() {
        this.bindSearchControl();
    }

    ngAfterViewInit() {
        fromEvent(this.searchInput.nativeElement, 'focus')
            .pipe(take(1))
            .subscribe(() => {
                this.findMatches();
            });
    }

    public writeValue(value: Artist[]) {
        this.value$.next(value);
    }

    public registerOnChange(fn: (artists: Artist[]) => void) {
        this.propagateChange = fn;
    }

    public registerOnTouched() {}

    public deselectArtist(artist: Artist) {
        const newArtists = this.value$.value.filter(a => a.id !== artist.id);
        this.value$.next(newArtists);
        this.propagateChange(this.value$.value);
    }

    public selectArtist(artist: Artist) {
        if (this.value$.value.findIndex(a => a.id === artist.id) === -1) {
            this.value$.next([...this.value$.value, artist]);
            this.propagateChange(this.value$.value);
        }
        this.searchInput.nativeElement.blur();
    }

    private bindSearchControl() {
        this.searchControl.valueChanges
            .pipe(
                distinctUntilChanged(),
                debounceTime(250),
            ).subscribe(query => {
                if (typeof query !== 'string') {
                    return observableOf([]);
                }
                this.findMatches(query);
            });
    }

    public findMatches(query?: string) {
        this.search.suggestArtists({query, limit: 7})
            .subscribe(r => this.searchResults$.next(r.artists));
    }

    public displayFn(_) {
        return null;
    }
}
