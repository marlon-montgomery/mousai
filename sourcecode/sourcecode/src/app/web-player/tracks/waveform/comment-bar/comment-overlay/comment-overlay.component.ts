import {ChangeDetectionStrategy, Component, Inject, OnInit, Optional} from '@angular/core';
import {OVERLAY_PANEL_DATA} from '@common/core/ui/overlay-panel/overlay-panel-data';
import {OverlayPanelRef} from '@common/core/ui/overlay-panel/overlay-panel-ref';
import {TrackComment} from '../../../../../models/TrackComment';

@Component({
    selector: 'comment-overlay',
    templateUrl: './comment-overlay.component.html',
    styleUrls: ['./comment-overlay.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush
})
export class CommentOverlayComponent implements OnInit {

    constructor(
        @Inject(OVERLAY_PANEL_DATA) @Optional() public data: {comment: TrackComment},
        private overlayPanelRef: OverlayPanelRef
    ) {}

    ngOnInit() {
    }

}
