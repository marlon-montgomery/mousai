import {
    ChangeDetectionStrategy,
    Component,
    ElementRef,
    HostBinding,
    Input,
    ViewChild
} from '@angular/core';
import {TrackComment} from '../../../../../models/TrackComment';
import {FormattedDuration} from '../../../../player/formatted-duration.service';
import {TrackCommentsService} from '../../../track-comments.service';
import {BehaviorSubject} from 'rxjs';
import {CurrentUser} from '@common/auth/current-user';
import {TRACK_MODEL} from '../../../../../models/Track';

@Component({
    selector: 'comment-list-item',
    templateUrl: './comment-list-item.component.html',
    styleUrls: ['./comment-list-item.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush
})
export class CommentListItemComponent {
    @Input() comment: TrackComment;
    @Input() parent: TrackComment;
    @ViewChild('deleteBtn', {read: ElementRef}) deleteButton: ElementRef<HTMLElement>;
    public commentFormVisible$ = new BehaviorSubject<boolean>(false);

    @HostBinding('style.padding-left') get paddingLeft() {
        return (this.comment.depth * 25) + 'px';
    }

    @HostBinding('class.nested') get nested() {
        return this.comment.depth;
    }

    constructor(
        public trackDuration: FormattedDuration,
        public trackComments: TrackCommentsService,
        public currentUser: CurrentUser,
    ) {}

    public postedAt(position: number): string {
        if (this.trackComments.mediaItem.model_type === TRACK_MODEL) {
            return this.trackDuration.fromSeconds((position / 100) * (this.trackComments.mediaItem.duration / 1000));
        }
        return null;
    }

    public toggleNewCommentForm() {
        this.commentFormVisible$.next(!this.commentFormVisible$.value);
    }

    public hideNewCommentForm() {
        this.commentFormVisible$.next(false);
    }
}
