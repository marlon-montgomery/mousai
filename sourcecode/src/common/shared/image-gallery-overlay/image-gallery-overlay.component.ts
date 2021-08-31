import {
    ChangeDetectionStrategy,
    Component,
    Inject,
    OnInit,
    Optional,
    ViewEncapsulation
} from '@angular/core';
import {BehaviorSubject} from 'rxjs';
import {matDialogAnimations} from '@angular/material/dialog';
import {OVERLAY_PANEL_DATA} from '@common/core/ui/overlay-panel/overlay-panel-data';
import {OverlayPanelRef} from '@common/core/ui/overlay-panel/overlay-panel-ref';

interface ImageGalleryOverlayData {
    activeIndex: number;
    images: {url: string}[];
}

@Component({
    selector: 'image-gallery-overlay',
    templateUrl: './image-gallery-overlay.component.html',
    styleUrls: ['./image-gallery-overlay.component.scss'],
    encapsulation: ViewEncapsulation.None,
    changeDetection: ChangeDetectionStrategy.OnPush,
    host: {
        '[@dialogContainer]': `'enter'`
    },
    animations: [
        matDialogAnimations.dialogContainer,
    ]
})
export class ImageGalleryOverlayComponent implements OnInit {
    public activeIndex = this.data.activeIndex || 0;
    public activeImage$ = new BehaviorSubject<string>(null);

    constructor(
        @Inject(OVERLAY_PANEL_DATA) @Optional() public data: ImageGalleryOverlayData,
        private overlayPanelRef: OverlayPanelRef,
    ) {}

    ngOnInit() {
        this.setActiveImage();
    }

    public close() {
        this.overlayPanelRef.close();
    }

    public showNext() {
        const nextIndex = this.activeIndex + 1;
        this.activeIndex = nextIndex > (this.data.images.length - 1) ? 0 : nextIndex;
        this.setActiveImage();
    }

    public showPrevious() {
        const prevIndex = this.activeIndex - 1;
        this.activeIndex = prevIndex < 0 ? (this.data.images.length - 1) : prevIndex;
        this.setActiveImage();
    }

    private setActiveImage() {
        this.activeImage$.next(this.data.images[this.activeIndex]?.url);
    }
}
