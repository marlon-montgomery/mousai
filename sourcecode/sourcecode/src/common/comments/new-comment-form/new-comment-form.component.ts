import {
    AfterViewInit,
    ChangeDetectionStrategy,
    Component,
    ElementRef,
    EventEmitter,
    Input,
    Output,
    ViewChild,
} from '@angular/core';
import {FormControl} from '@angular/forms';
import {CurrentUser} from '@common/auth/current-user';
import {CommentsService} from '@common/shared/comments/comments.service';
import {Comment} from '@common/shared/comments/comment';
import {BehaviorSubject} from 'rxjs';
import {Toast} from '@common/core/ui/toast.service';

export interface NewCommentPayload {
    newComment: Comment;
    inReplyTo: Comment;
}

@Component({
    selector: 'new-comment-form',
    templateUrl: './new-comment-form.component.html',
    styleUrls: ['./new-comment-form.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush,
    host: {class: 'comment-marker-ancestor'},
})
export class NewCommentFormComponent implements AfterViewInit {
    @Input() inReplyTo: Comment;
    @Input() commentable: {id: number; model_type: string};
    @Input() autoFocus = false;
    @Output() created = new EventEmitter<NewCommentPayload>();
    @ViewChild('input', {static: true}) inputEl: ElementRef<HTMLInputElement>;
    commentControl = new FormControl();
    loading$ = new BehaviorSubject<boolean>(false);

    constructor(
        public comments: CommentsService,
        public currentUser: CurrentUser,
        private toast: Toast
    ) {}

    ngAfterViewInit() {
        if (this.autoFocus) {
            this.inputEl.nativeElement.focus();
        }
    }

    submit() {
        this.comments
            .create({
                content: this.commentControl.value,
                commentable_id: this.commentable.id,
                commentable_type: this.commentable.model_type,
                inReplyTo: this.inReplyTo,
            })
            .subscribe(response => {
                this.commentControl.reset();
                this.created.emit({
                    newComment: response.comment,
                    inReplyTo: this.inReplyTo,
                });
                this.toast.open('Comment posted.');
            });
    }
}
