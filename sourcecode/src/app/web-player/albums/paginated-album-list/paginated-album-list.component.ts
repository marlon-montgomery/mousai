import {
    AfterViewInit,
    ChangeDetectionStrategy,
    Component,
    Input,
    NgZone,
    OnDestroy,
    OnInit
} from '@angular/core';
import {filter, finalize} from 'rxjs/operators';
import {InfiniteScroll} from '@common/core/ui/infinite-scroll/infinite.scroll';
import {BehaviorSubject, Subscription} from 'rxjs';
import {PaginationResponse} from '@common/core/types/pagination/pagination-response';
import {PaginatedBackendResponse} from '@common/core/types/pagination/paginated-backend-response';
import {Album} from '../../../models/Album';
import {WebPlayerState} from '../../web-player-state.service';
import {Albums} from '../albums.service';

@Component({
    selector: 'paginated-album-list',
    templateUrl: './paginated-album-list.component.html',
    styleUrls: ['./paginated-album-list.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush
})
export class PaginatedAlbumListComponent extends InfiniteScroll implements OnInit, AfterViewInit, OnDestroy {
    public pagination$ = new BehaviorSubject<PaginationResponse<Album>>(null);
    public loading$ = new BehaviorSubject<boolean>(false);
    private albumDeleteSub: Subscription;
    @Input() loadFn: (page: number) => PaginatedBackendResponse<Album>;

    constructor(
        protected zone: NgZone,
        protected state: WebPlayerState,
        protected albums: Albums,
    ) {
        super();

        this.albumDeleteSub = this.albums.albumsDeleted$
            .pipe(filter(() => !!this.pagination$.value))
            .subscribe(albumIds => {
                const newAlbums = this.pagination$.value.data.filter(album => {
                    return !albumIds.includes(album.id);
                });
                this.pagination$.next({
                    ...this.pagination$.value,
                    data: newAlbums
                });
            });
    }

    ngOnInit() {
        super.ngOnInit();
        this.loadMoreItems();
    }


    ngOnDestroy() {
        this.albumDeleteSub.unsubscribe();
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

    currentData(): Album[] {
        return this.pagination$.value?.data ?? [];
    }
}
