import {
    ChangeDetectionStrategy,
    Component,
    Inject,
    Optional,
} from '@angular/core';
import {GRADIENT_LIST} from '@common/shared/form-controls/background-selector/background-selector-gradient/gradient-list';
import {OVERLAY_PANEL_DATA} from '@common/core/ui/overlay-panel/overlay-panel-data';
import {OverlayPanelRef} from '@common/core/ui/overlay-panel/overlay-panel-ref';
import {BackgroundConfig} from '@common/shared/form-controls/background-selector/background-list';

@Component({
    selector: 'background-selector-gradient',
    templateUrl: './background-selector-gradient.component.html',
    styleUrls: ['./background-selector-gradient.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class BackgroundSelectorGradientComponent {
    gradients = GRADIENT_LIST;

    constructor(
        @Inject(OVERLAY_PANEL_DATA)
        @Optional()
        public data: {active?: BackgroundConfig},
        private overlayPanelRef: OverlayPanelRef
    ) {}

    selectGradient(gradient: BackgroundConfig) {
        this.overlayPanelRef.close(gradient);
    }
}
