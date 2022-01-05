import {
    ChangeDetectionStrategy,
    Component,
    Input,
    NgZone,
    OnInit,
} from '@angular/core';
import {InfiniteScroll} from '@common/core/ui/infinite-scroll/infinite.scroll';
import {BehaviorSubject} from 'rxjs';
import {PaginationResponse} from '@common/core/types/pagination/pagination-response';
import {Comment} from '@common/shared/comments/comment';
import {finalize} from 'rxjs/operators';
import {CommentsService} from '@common/shared/comments/comments.service';
import {CurrentUser} from '@common/auth/current-user';
import {NewCommentPayload} from './new-comment-form/new-comment-form.component';

@Component({
    selector: 'comment-list',
    templateUrl: './comment-list.component.html',
    styleUrls: ['./comment-list.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class CommentListComponent extends InfiniteScroll implements OnInit {
    @Input() commentable: {id: number; model_type: string};
    loading$ = new BehaviorSubject<boolean>(false);
    pagination$ = new BehaviorSubject<PaginationResponse<Comment>>(null);
    commentCount$ = new BehaviorSubject<number>(0);

    constructor(
        protected zone: NgZone,
        protected comments: CommentsService,
        private currentUser: CurrentUser
    ) {
        super();
    }

    ngOnInit() {
        super.ngOnInit();
        this.loadMoreItems();
    }

    addComment(payload: NewCommentPayload) {
        this.commentCount$.next(this.commentCount$.value + 1);
        payload.newComment.user = this.currentUser.getModel();
        if (payload.inReplyTo) {
            const i = this.pagination$.value.data.findIndex(
                c => c.id === payload.inReplyTo.id
            );
            this.pagination$.value.data.splice(i + 1, 0, payload.newComment);
            this.pagination$.next({...this.pagination$.value});
        } else {
            // paginated comments (if on main track page)
            if (this.pagination$.value) {
                this.pagination$.value.data.unshift(payload.newComment);
                this.pagination$.next({...this.pagination$.value});
            }
        }
    }

    deleteComment(commentId: number) {
        this.comments.delete([commentId]).subscribe(response => {
            const newData = [];
            this.pagination$.value.data.forEach(c => {
                if (!response.allDeleted.includes(c.id)) {
                    if (response.allMarkedAsDeleted.includes(c.id)) {
                        c = {
                            ...c,
                            content: null,
                            user: null,
                            deleted: true,
                        };
                    }
                    newData.push(c);
                }
            });
            this.commentCount$.next(this.commentCount$.value - 1);
            this.pagination$.next({
                ...this.pagination$.value,
                data: newData,
            });
        });
    }

    canLoadMore() {
        return (
            this.pagination$.value &&
            this.pagination$.value.current_page < this.pagination$.value.total
        );
    }

    protected isLoading() {
        return this.loading$.value;
    }

    protected loadMoreItems() {
        this.loading$.next(true);
        return this.comments
            .forCommentable({
                commentableId: this.commentable.id,
                commentableType: this.commentable.model_type,
                page: (this.pagination$.value?.current_page || 0) + 1,
            })
            .pipe(finalize(() => this.loading$.next(false)))
            .subscribe(response => {
                this.commentCount$.next(response.commentCount);
                this.pagination$.next({
                    ...response.pagination,
                    data: [
                        ...(this.pagination$.value?.data || []),
                        ...response.pagination.data,
                    ],
                });
            });
    }
}
