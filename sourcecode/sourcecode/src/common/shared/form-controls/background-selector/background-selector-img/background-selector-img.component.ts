import {
    ChangeDetectionStrategy,
    Component,
    ElementRef,
    HostListener,
    Output,
    EventEmitter,
    Input,
    OnChanges,
} from '@angular/core';
import {openUploadWindow} from '@common/uploads/utils/open-upload-window';
import {UploadInputTypes} from '@common/uploads/upload-input-config';
import {UploadQueueService} from '@common/uploads/upload-queue/upload-queue.service';
import {AppearanceImageUploadValidator} from '@common/admin/appearance/appearance-image-input/appearance-image-upload-validator';
import {BackgroundUrlPipe} from '@common/core/ui/format-pipes/background-url.pipe';
import {
    BackgroundConfig,
    uploadedImgBg,
} from '@common/shared/form-controls/background-selector/background-list';

@Component({
    selector: 'background-selector-img',
    templateUrl: './background-selector-img.component.html',
    styleUrls: ['./background-selector-img.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class BackgroundSelectorImgComponent implements OnChanges {
    @Output() imgUploaded = new EventEmitter<BackgroundConfig>();
    @Input() selectedBg: BackgroundConfig;

    ngOnChanges() {
        this.el.nativeElement.style.backgroundImage =
            this.selectedBg && this.selectedBg.configId === 'uploadedImg'
                ? this.bgUrl.transform(this.selectedBg.backgroundImage)
                : null;
    }

    constructor(
        private uploadQueue: UploadQueueService,
        private imgValidator: AppearanceImageUploadValidator,
        private el: ElementRef<HTMLElement>,
        private bgUrl: BackgroundUrlPipe
    ) {}

    @HostListener('click', ['$event.target'])
    openUploadDialog() {
        openUploadWindow({
            types: [UploadInputTypes.image],
        }).then(files => {
            this.uploadQueue
                .start(files, {
                    validator: this.imgValidator,
                    httpParams: {diskPrefix: 'biolink', disk: 'public'},
                })
                .subscribe(response => {
                    this.imgUploaded.emit({
                        ...uploadedImgBg,
                        backgroundImage: response.fileEntry.url,
                    });
                });
        });
    }
}
