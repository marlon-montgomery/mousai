import {
    AfterViewInit,
    ChangeDetectionStrategy,
    Component,
    ElementRef,
    Input,
    NgZone,
    OnDestroy,
    OnInit
} from '@angular/core';
import {BehaviorSubject, Subscription} from 'rxjs';
import {Tracks} from '../tracks.service';
import {filter, finalize} from 'rxjs/operators';
import {PaginationResponse} from '@common/core/types/pagination/pagination-response';
import {Track} from '../../../models/Track';
import {InfiniteScroll} from '@common/core/ui/infinite-scroll/infinite.scroll';
import {PaginatedBackendResponse} from '@common/core/types/pagination/paginated-backend-response';
import {WebPlayerState} from '../../web-player-state.service';
import {User} from '@common/core/types/models/User';

@Component({
    selector: 'paginated-track-list',
    templateUrl: './paginated-track-list.component.html',
    styleUrls: ['./paginated-track-list.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class PaginatedTrackListComponent extends InfiniteScroll implements OnInit, AfterViewInit, OnDestroy {
    public pagination$ = new BehaviorSubject<PaginationResponse<Track>>(null);
    public loading$ = new BehaviorSubject<boolean>(false);
    private trackDeleteSub: Subscription;
    @Input() reposter: User;
    @Input() disablePagination = false;
    @Input() hideNoResultsMessage = false;
    @Input() initialData: PaginationResponse<Track>;
    @Input() loadFn: (page: number) => PaginatedBackendResponse<Track>;

    constructor(
        protected el: ElementRef<HTMLElement>,
        protected zone: NgZone,
        protected state: WebPlayerState,
        protected tracks: Tracks,
    ) {
        super();

        this.trackDeleteSub = this.tracks.tracksDeleted$
            .pipe(filter(() => !!this.pagination$.value))
            .subscribe(deletedTracks => {
                const newTracks = this.pagination$.value.data.filter(track => {
                    return !deletedTracks.includes(track.id);
                });
                this.pagination$.next({
                    ...this.pagination$.value,
                    data: newTracks
                });
            });
    }

    ngOnInit() {
        if ( ! this.disablePagination) {
            super.ngOnInit();
        }
        if (this.initialData) {
            this.pagination$.next(this.initialData);
        } else {
            this.loadMoreItems();
        }
    }

    ngOnDestroy() {
        this.trackDeleteSub.unsubscribe();
        super.ngOnDestroy();
    }

    ngAfterViewInit() {
        this.el = this.state.scrollContainer;
        super.ngOnInit();
    }

    protected canLoadMore(): boolean {
        return this.pagination$.value?.last_page >= this.pagination$.value?.current_page;
    }

    protected isLoading(): boolean {
        return this.loading$.value;
    }

    protected loadMoreItems() {
        this.loading$.next(true);
        this.loadFn(this.currentPage() + 1)
            .pipe(finalize(() => this.loading$.next(false)))
            .subscribe(response => {
                this.pagination$.next({
                    ...response.pagination,
                    data: [...this.currentData(), ...response.pagination.data],
                });
            });
    }

    currentPage(): number {
        return this.pagination$.value?.current_page ?? 0;
    }

    currentData(): Track[] {
        return this.pagination$.value?.data ?? [];
    }
}
