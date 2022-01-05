import {
    ChangeDetectionStrategy,
    Component,
    HostBinding, Inject,
    OnInit, Optional,
} from '@angular/core';
import {OverlayPanelRef} from '@common/core/ui/overlay-panel/overlay-panel-ref';
import {FormBuilder} from '@angular/forms';
import {OVERLAY_PANEL_DATA} from '@common/core/ui/overlay-panel/overlay-panel-data';

export interface BackgroundOverlayData {
    initialValues: Record<string, string>;
}

@Component({
    selector: 'background-overlay',
    templateUrl: './background-overlay.component.html',
    styleUrls: ['./background-overlay.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush,
    host: {class: 'builder-overlay'},
})
export class BackgroundOverlayComponent implements OnInit {
    @HostBinding('class.compact') compact = true;

    bgControl = this.fb.control(null);
    form = this.fb.group({
        backgroundRepeat: null,
        backgroundPosition: null,
        backgroundColor: null,
        backgroundAttachment: null,
        backgroundSize: null,
        backgroundImage: null,
        color: null,
        configLabel: null,
    });

    constructor(
        public overlayPanelRef: OverlayPanelRef,
        @Inject(OVERLAY_PANEL_DATA) @Optional() public data: BackgroundOverlayData,
        private fb: FormBuilder
    ) {}

    ngOnInit() {
        this.form.patchValue(this.data.initialValues);
        this.bgControl.valueChanges.subscribe(v => this.form.patchValue(v));
        this.form.valueChanges.subscribe(value => {
            this.overlayPanelRef.emitValue(value);
        });
    }

    setBgPosition(value: string) {
        this.form.patchValue({backgroundPosition: value});
    }
}
