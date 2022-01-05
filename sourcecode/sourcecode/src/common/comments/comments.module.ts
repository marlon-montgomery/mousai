import {NgModule} from '@angular/core';
import {CommentListComponent} from '@common/comments/comment-list.component';
import {CommentListItemComponent} from '@common/comments/comment-list-item/comment-list-item.component';
import {NewCommentFormComponent} from '@common/comments/new-comment-form/new-comment-form.component';
import {ConfirmCommentDeletionPopoverComponent} from '@common/comments/confirm-comment-deletion-popover/confirm-comment-deletion-popover.component';
import {CommonModule} from '@angular/common';
import {TranslationsModule} from '@common/core/translations/translations.module';
import {MatIconModule} from '@angular/material/icon';
import {MatButtonModule} from '@angular/material/button';
import {LoadingIndicatorModule} from '@common/core/ui/loading-indicator/loading-indicator.module';
import {RouterModule} from '@angular/router';
import {FormsModule, ReactiveFormsModule} from '@angular/forms';

@NgModule({
    imports: [
        CommonModule,
        TranslationsModule,
        LoadingIndicatorModule,
        RouterModule,
        ReactiveFormsModule,
        FormsModule,

        //
        MatIconModule,
        MatButtonModule,
    ],
    declarations: [
        CommentListComponent,
        CommentListItemComponent,
        NewCommentFormComponent,
        ConfirmCommentDeletionPopoverComponent,
    ],
    exports: [
        CommentListComponent,
        CommentListItemComponent,
        NewCommentFormComponent,
        ConfirmCommentDeletionPopoverComponent,
    ],
})
export class CommentsModule {}
