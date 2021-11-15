import {
    AfterViewInit,
    ChangeDetectionStrategy,
    ChangeDetectorRef,
    Component,
    ElementRef,
    Input,
    NgZone,
    OnDestroy,
    OnInit
} from '@angular/core';
import {BehaviorSubject} from 'rxjs';
import {PaginationResponse} from '@common/core/types/pagination/pagination-response';
import {PaginatedBackendResponse} from '@common/core/types/pagination/paginated-backend-response';
import {WebPlayerState} from '../../web-player-state.service';
import {CurrentUser} from '@common/auth/current-user';
import {WebPlayerUrls} from '../../web-player-urls.service';
import {UserProfileService} from '../../users/user-profile.service';
import {finalize} from 'rxjs/operators';
import {InfiniteScroll} from '@common/core/ui/infinite-scroll/infinite.scroll';
import {Playlist} from '../../../models/Playlist';

@Component({
    selector: 'paginated-playlist-tab',
    templateUrl: './paginated-playlist-tab.component.html',
    styleUrls: ['./paginated-playlist-tab.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush
})
export class PaginatedPlaylistTabComponent extends InfiniteScroll implements OnInit, OnDestroy, AfterViewInit {
    public pagination$ = new BehaviorSubject<PaginationResponse<Playlist>>(null);
    public loading$ = new BehaviorSubject<boolean>(false);
    @Input() loadFn: (page: number) => PaginatedBackendResponse<Playlist>;

    constructor(
        protected el: ElementRef<HTMLElement>,
        protected zone: NgZone,
        protected state: WebPlayerState,
        protected currentUser: CurrentUser,
        public urls: WebPlayerUrls,
        public profile: UserProfileService,
        public cd: ChangeDetectorRef,
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

    currentData(): Playlist[] {
        return this.pagination$.value?.data ?? [];
    }
}
