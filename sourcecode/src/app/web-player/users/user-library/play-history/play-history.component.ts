import {Component, NgZone, OnDestroy, OnInit} from '@angular/core';
import {WebPlayerUrls} from '../../../web-player-urls.service';
import {WebPlayerState} from '../../../web-player-state.service';
import {InfiniteScroll} from '@common/core/ui/infinite-scroll/infinite.scroll';
import {Track} from '../../../../models/Track';
import {CurrentUser} from '@common/auth/current-user';
import {queueId} from '../../../player/queue-id';
import {User} from '@common/core/types/models/User';
import {DatatableService} from '@common/datatable/datatable.service';

@Component({
    selector: 'play-history',
    templateUrl: './play-history.component.html',
    styleUrls: ['./play-history.component.scss'],
    providers: [DatatableService],

})
export class PlayHistoryComponent extends InfiniteScroll implements OnInit, OnDestroy {
    constructor(
        public datatable: DatatableService<Track>,
        public urls: WebPlayerUrls,
        private state: WebPlayerState,
        private currentUser: CurrentUser,
        protected zone: NgZone,
    ) {
        super();
        this.datatable.paginator.dontUpdateQueryParams = true;
    }

    ngOnInit() {
        this.datatable.init({
            uri: `track/plays/${this.currentUser.get('id')}`,
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

    public queueId() {
        return queueId(this.currentUser.getModel() as User, 'playHistory', this.datatable.getCurrentParams());
    }
}
