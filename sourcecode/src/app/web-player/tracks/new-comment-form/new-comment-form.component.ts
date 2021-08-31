import {
    AfterViewInit,
    ChangeDetectionStrategy,
    Component,
    ElementRef,
    EventEmitter,
    Input,
    Output,
    ViewChild
} from '@angular/core';
import {FormControl} from '@angular/forms';
import {CurrentUser} from '@common/auth/current-user';
import {TrackCommentsService} from '../track-comments.service';
import {TrackComment} from '../../../models/TrackComment';
import {combineLatest} from 'rxjs';

@Component({
    selector: 'new-comment-form',
    templateUrl: './new-comment-form.component.html',
    styleUrls: ['./new-comment-form.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush,
    host: {class: 'comment-marker-ancestor'},
})
export class NewCommentFormComponent implements AfterViewInit {
    @Input() inReplyTo: TrackComment;
    @Input() autoFocus = false;
    @Input() focusOnMarkerMove = false;
    @Output() created = new EventEmitter<TrackComment>();
    @ViewChild('input', {static: true}) inputEl: ElementRef<HTMLInputElement>;
    public commentControl = new FormControl();

    constructor(
        public trackComments: TrackCommentsService,
        public currentUser: CurrentUser,
    ) {}

    ngAfterViewInit() {
        if (this.autoFocus) {
            this.inputEl.nativeElement.focus();
        }
        if (this.focusOnMarkerMove) {
            combineLatest([
                this.trackComments.markerPosition$,
                this.trackComments.markerActive$,
            ]).subscribe(values => {
                if (values[1]) {
                    this.inputEl.nativeElement.focus();
                }
            });
        }
    }

    public submit() {
        this.trackComments.create(this.commentControl.value, this.inReplyTo)
            .then(comment => {
                this.commentControl.reset();
                this.created.emit(comment);
            });
    }
}
