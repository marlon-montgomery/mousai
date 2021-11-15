import {
    AfterViewInit,
    ChangeDetectionStrategy,
    Component,
    Input,
    NgZone,
    OnDestroy,
    OnInit
} from '@angular/core';
import {BehaviorSubject, Subscription} from 'rxjs';
import {PaginationResponse} from '@common/core/types/pagination/pagination-response';
import {PaginatedBackendResponse} from '@common/core/types/pagination/paginated-backend-response';
import {WebPlayerState} from '../../web-player-state.service';
import {Albums} from '../../albums/albums.service';
import {finalize} from 'rxjs/operators';
import {InfiniteScroll} from '@common/core/ui/infinite-scroll/infinite.scroll';
import {Artist} from '../../../models/Artist';

@Component({
    selector: 'paginated-artist-list',
    templateUrl: './paginated-artist-list.component.html',
    styleUrls: ['./paginated-artist-list.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush
})
export class PaginatedArtistListComponent extends InfiniteScroll implements OnInit, AfterViewInit, OnDestroy {
    public pagination$ = new BehaviorSubject<PaginationResponse<Artist>>(null);
    public loading$ = new BehaviorSubject<boolean>(false);
    private albumDeleteSub: Subscription;
    @Input() loadFn: (page: number) => PaginatedBackendResponse<Artist>;

    constructor(
        protected zone: NgZone,
        protected state: WebPlayerState,
        protected albums: Albums,
    ) {
        super();
    }

    ngOnInit() {
        super.ngOnInit();
        this.loadMoreItems();
    }

    ngOnDestroy() {
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

    currentData(): Artist[] {
        return this.pagination$.value?.data ?? [];
    }

}
