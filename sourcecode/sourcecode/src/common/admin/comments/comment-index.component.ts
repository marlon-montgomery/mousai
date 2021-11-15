import {ChangeDetectionStrategy, Component, OnDestroy, OnInit} from '@angular/core';
import {CurrentUser} from '@common/auth/current-user';
import {Settings} from '@common/core/config/settings.service';
import {Toast} from '@common/core/ui/toast.service';
import {HttpErrors} from '@common/core/http/errors/http-errors.enum';
import {Role} from '@common/core/types/models/Role';
import {BackendErrorResponse} from '@common/core/types/backend-error-response';
import {Observable} from 'rxjs';
import {CommentsService} from '@common/shared/comments/comments.service';
import {DatatableService} from '@common/datatable/datatable.service';
import {Comment} from '@common/shared/comments/comment';
import {COMMENT_INDEX_FILTERS} from './comment-index-filters';
import {UrlGeneratorService} from '@common/core/services/url-generator.service';

@Component({
    selector: 'user-index',
    templateUrl: './comment-index.component.html',
    styleUrls: ['./comment-index.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush,
    providers: [DatatableService],
})
export class CommentIndexComponent implements OnInit, OnDestroy {
    comments$ = this.datatable.data$ as Observable<Comment[]>;
    filters = COMMENT_INDEX_FILTERS;

    constructor(
        private comments: CommentsService,
        public currentUser: CurrentUser,
        public settings: Settings,
        private toast: Toast,
        public datatable: DatatableService<Comment>,
        public url: UrlGeneratorService,
    ) {}

    ngOnInit() {
        this.datatable.init({
            uri: CommentsService.BASE_URI,
            staticParams: {
                with: ['commentable'],
            }
        });
    }

    ngOnDestroy() {
        this.datatable.destroy();
    }

    public makeRolesList(roles: Role[]): string {
        return roles.slice(0, 3).map(role => role.name).join(', ');
    }

    public maybeDeleteComments(comment?: Comment) {
        this.datatable.confirmResourceDeletion('comments')
            .subscribe(() => {
                const ids = comment ? [comment.id] : this.datatable.selectedRows$.value;
                this.comments.delete(ids).subscribe(() => {
                    this.datatable.reset();
                    this.toast.open('Comments deleted');
                }, (errResponse: BackendErrorResponse) => {
                    this.toast.open(errResponse.message || HttpErrors.Default);
                });
            });
    }

    public restoreComment(comment: Comment) {
        this.comments.restore([comment.id]).subscribe(() => {
            this.datatable.reset();
            this.toast.open('Comment restored');
        });
    }
}
