import {Component, NgZone, OnDestroy, OnInit} from '@angular/core';
import {Playlist} from '../../../../models/Playlist';
import {Settings} from '@common/core/config/settings.service';
import {WebPlayerState} from '../../../web-player-state.service';
import {InfiniteScroll} from '@common/core/ui/infinite-scroll/infinite.scroll';
import {CurrentUser} from '@common/auth/current-user';
import {DatatableService} from '@common/datatable/datatable.service';
import {map} from 'rxjs/operators';

@Component({
    selector: 'library-playlists',
    templateUrl: './library-playlists.component.html',
    styleUrls: ['./library-playlists.component.scss'],
    host: {class: 'user-library-page'},
    providers: [DatatableService],
})
export class LibraryPlaylistsComponent extends InfiniteScroll implements OnInit, OnDestroy {
    public totalPlaylists$ = this.datatable.paginator.response$.pipe(map(r =>  r?.pagination?.total || 0));
    constructor(
        private settings: Settings,
        private state: WebPlayerState,
        protected zone: NgZone,
        private currentUser: CurrentUser,
        public datatable: DatatableService<Playlist>,
    ) {
        super();
        this.datatable.paginator.dontUpdateQueryParams = true;
    }

    ngOnInit() {
        this.datatable.init({
            uri: `users/${this.currentUser.get('id')}/playlists`,
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

    public trackByFn = (i: number, playlist: Playlist) => playlist.id;
}
