import {Component, NgZone, OnDestroy, OnInit} from '@angular/core';
import {UserLibrary} from '../user-library.service';
import {InfiniteScroll} from '@common/core/ui/infinite-scroll/infinite.scroll';
import {WebPlayerState} from '../../../web-player-state.service';
import {CurrentUser} from '@common/auth/current-user';
import {queueId} from '../../../player/queue-id';
import {User} from '@common/core/types/models/User';
import {Track, TRACK_MODEL} from '../../../../models/Track';
import {Player} from '../../../player/player.service';
import {DatatableService} from '@common/datatable/datatable.service';

@Component({
    selector: 'library-tracks',
    templateUrl: './library-tracks.component.html',
    styleUrls: ['./library-tracks.component.scss'],
    host: {class: 'user-library-page'},
    providers: [DatatableService],
})
export class LibraryTracksComponent extends InfiniteScroll implements OnInit, OnDestroy {
    constructor(
        public library: UserLibrary,
        private state: WebPlayerState,
        private currentUser: CurrentUser,
        public player: Player,
        public datatable: DatatableService<Track>,
        protected zone: NgZone,
    ) {
        super();
        this.datatable.paginator.dontUpdateQueryParams = true;
    }

    ngOnInit() {
        this.el = this.state.scrollContainer;
        this.datatable.init({
            uri: 'users/me/liked-tracks',
            infiniteScroll: true,
        });
        super.ngOnInit();
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

    public queueId() {
        return queueId(this.currentUser.getModel() as User, 'libraryTracks', this.datatable.getCurrentParams());
    }

    public totalCount(): number {
        return this.library.count(TRACK_MODEL);
    }
}
