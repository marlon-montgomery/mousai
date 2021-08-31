import {ChangeDetectionStrategy, Component} from '@angular/core';
import {OverlayPanelRef} from '@common/core/ui/overlay-panel/overlay-panel-ref';

@Component({
    selector: 'confirm-comment-deletion-popover',
    templateUrl: './confirm-comment-deletion-popover.component.html',
    styleUrls: ['./confirm-comment-deletion-popover.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush
})
export class ConfirmCommentDeletionPopoverComponent {
    constructor(
        private overlayPanelRef: OverlayPanelRef,
    ) {}

    public close(confirmed: boolean) {
        this.overlayPanelRef.close(confirmed);
    }
}
