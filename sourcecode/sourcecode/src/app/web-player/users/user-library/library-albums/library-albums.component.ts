import {Component, NgZone, OnDestroy, OnInit} from '@angular/core';
import {Album} from '../../../../models/Album';
import {WebPlayerUrls} from '../../../web-player-urls.service';
import {InfiniteScroll} from '@common/core/ui/infinite-scroll/infinite.scroll';
import {WebPlayerState} from '../../../web-player-state.service';
import {DatatableService} from '@common/datatable/datatable.service';
import {map} from 'rxjs/operators';

@Component({
    selector: 'library-albums',
    templateUrl: './library-albums.component.html',
    styleUrls: ['./library-albums.component.scss'],
    providers: [DatatableService],
})
export class LibraryAlbumsComponent extends InfiniteScroll implements OnInit, OnDestroy {
    public totalAlbums$ = this.datatable.paginator.response$.pipe(map(r => r.pagination.total));
    constructor(
        public datatable: DatatableService<Album>,
        public urls: WebPlayerUrls,
        private state: WebPlayerState,
        protected zone: NgZone,
    ) {
        super();
        this.datatable.paginator.dontUpdateQueryParams = true;
    }

    ngOnInit() {
        this.datatable.init({
            uri: 'users/me/liked-albums',
            infiniteScroll: true,
        });
        this.el = this.state.scrollContainer;
        super.ngOnInit();
    }

    ngOnDestroy() {
        this.datatable.destroy();
        super.ngOnDestroy();
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

    public trackByFn = (i: number, album: Album) => album.id;
}
