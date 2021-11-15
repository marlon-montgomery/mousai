import {
    ChangeDetectionStrategy,
    ChangeDetectorRef,
    Component,
    Input,
    OnChanges,
    OnDestroy,
    OnInit
} from '@angular/core';
import {UploadQueueItem, UploadQueueItemProgress} from '@common/uploads/upload-queue/upload-queue-item';
import {throttleTime} from 'rxjs/operators';
import {animationFrameScheduler, Subscription} from 'rxjs';

@Component({
    selector: 'track-upload-header',
    templateUrl: './track-upload-header.component.html',
    styleUrls: ['./track-upload-header.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class TrackUploadHeaderComponent implements OnChanges, OnDestroy {
    @Input() upload: UploadQueueItem;
    private subscription: Subscription;
    public progress: UploadQueueItemProgress;

    constructor(private cd: ChangeDetectorRef) {}


    ngOnChanges() {
        if (this.subscription) {
            this.subscription.unsubscribe();
        }
        this.subscription = this.upload.progress$.pipe(
            // material progress bar animation lasts 250ms
            throttleTime(260, animationFrameScheduler, {leading: true, trailing: true}),
        ).subscribe(progress => {
            this.progress = progress;
            this.cd.detectChanges();
        });
    }

    ngOnDestroy() {
        this.subscription.unsubscribe();
    }
}
