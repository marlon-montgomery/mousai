import {ChangeDetectionStrategy, Component, NgZone} from '@angular/core';
import {TrackCommentsService} from '../../../track-comments.service';
import {InfiniteScroll} from '@common/core/ui/infinite-scroll/infinite.scroll';
import {WebPlayerState} from '../../../../web-player-state.service';

@Component({
    selector: 'comment-list',
    templateUrl: './comment-list.component.html',
    styleUrls: ['./comment-list.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush
})
export class CommentListComponent extends InfiniteScroll {
    constructor(
        public trackComments: TrackCommentsService,
        private state: WebPlayerState,
        protected zone: NgZone,
    ) {
        super();
        this.el = this.state.scrollContainer;
    }

    public canLoadMore() {
        return this.trackComments.pagination$.value &&
            (this.trackComments.pagination$.value.current_page < this.trackComments.pagination$.value.total);
    }

    protected isLoading() {
        return this.trackComments.loading$.value;
    }

    protected loadMoreItems() {
        this.trackComments.loadMore();
    }
}
