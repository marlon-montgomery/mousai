import {
    ChangeDetectionStrategy,
    Component,
    ElementRef,
    EventEmitter,
    HostBinding,
    Input,
    Output,
    ViewChild,
} from '@angular/core';
import {BehaviorSubject} from 'rxjs';
import {CurrentUser} from '@common/auth/current-user';
import {Comment} from '@common/shared/comments/comment';
import {OverlayPanelRef} from '@common/core/ui/overlay-panel/overlay-panel-ref';
import {ConfirmCommentDeletionPopoverComponent} from '../confirm-comment-deletion-popover/confirm-comment-deletion-popover.component';
import {BOTTOM_POSITION} from '@common/core/ui/overlay-panel/positions/bottom-position';
import {OverlayPanel} from '@common/core/ui/overlay-panel/overlay-panel.service';
import {NewCommentPayload} from '../new-comment-form/new-comment-form.component';

@Component({
    selector: 'comment-list-item',
    templateUrl: './comment-list-item.component.html',
    styleUrls: ['./comment-list-item.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class CommentListItemComponent {
    @Input() commentable: {id: number; model_type: string};
    @Input() comment: Comment;
    @Input() parent: Comment;
    @Output() removed = new EventEmitter<number>();
    @Output() created = new EventEmitter<NewCommentPayload>();
    @ViewChild('deleteBtn', {read: ElementRef})
    deleteButton: ElementRef<HTMLElement>;
    commentFormVisible$ = new BehaviorSubject<boolean>(false);
    canDeleteAllComments: boolean;
    private confirmDeleteOverlayRef: OverlayPanelRef<ConfirmCommentDeletionPopoverComponent>;

    @HostBinding('style.padding-left') get paddingLeft() {
        return this.comment.depth * 25 + 'px';
    }

    @HostBinding('class.nested') get nested() {
        return this.comment.depth;
    }

    constructor(
        public currentUser: CurrentUser,
        private overlay: OverlayPanel
    ) {}

    toggleNewCommentForm() {
        this.commentFormVisible$.next(!this.commentFormVisible$.value);
    }

    hideNewCommentForm() {
        this.commentFormVisible$.next(false);
    }

    confirmDeletion(origin: ElementRef<HTMLElement>, comment: Comment) {
        if (this.confirmDeleteOverlayRef) {
            this.confirmDeleteOverlayRef.close();
            this.confirmDeleteOverlayRef = null;
        }
        const position = [...BOTTOM_POSITION];
        this.confirmDeleteOverlayRef = this.overlay.open(
            ConfirmCommentDeletionPopoverComponent,
            {
                origin,
                position,
                backdropClass: 'cdk-overlay-transparent-backdrop',
            }
        );

        this.confirmDeleteOverlayRef.afterClosed().subscribe(confirmed => {
            if (confirmed) {
                this.removed.emit(comment.id);
            }
        });
    }

    onCommentCreated(payload: NewCommentPayload) {
        this.created.emit(payload);
        this.hideNewCommentForm();
    }
}
