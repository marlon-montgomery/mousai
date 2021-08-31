import {Component, NgZone, OnDestroy, OnInit} from '@angular/core';
import {Artist} from '../../../../models/Artist';
import {WebPlayerUrls} from '../../../web-player-urls.service';
import {InfiniteScroll} from '@common/core/ui/infinite-scroll/infinite.scroll';
import {WebPlayerState} from '../../../web-player-state.service';
import {DatatableService} from '@common/datatable/datatable.service';
import {map} from 'rxjs/operators';

@Component({
    selector: 'library-artists',
    templateUrl: './library-artists.component.html',
    styleUrls: ['./library-artists.component.scss'],
    providers: [DatatableService],
})
export class LibraryArtistsComponent extends InfiniteScroll implements OnInit, OnDestroy {
    public totalArtists$ = this.datatable.paginator.response$.pipe(map(r => r.pagination.total));
    constructor(
        public urls: WebPlayerUrls,
        private state: WebPlayerState,
        protected zone: NgZone,
        public datatable: DatatableService<Artist>,
    ) {
        super();
        this.datatable.paginator.dontUpdateQueryParams = true;
    }

    ngOnInit() {
        this.datatable.init({
            uri: 'users/me/liked-artists',
            infiniteScroll: true,
        });
        this.el = this.state.scrollContainer;
        super.ngOnInit();
    }

    ngOnDestroy() {
        super.ngOnDestroy();
        this.datatable.destroy();
    }

    public canLoadMore() {
        return this.datatable.paginator.canLoadNextPage();
    }

    protected isLoading() {
        return this.datatable.paginator.loading$.value;
    }

    protected loadMoreItems() {
        this.datatable.paginator.nextPage();
    }

    public trackByFn = (i: number, artist: Artist) => artist.id;
}
