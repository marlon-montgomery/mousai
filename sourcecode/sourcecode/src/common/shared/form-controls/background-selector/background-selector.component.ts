import {ChangeDetectionStrategy, Component, ElementRef, Input} from '@angular/core';
import {
    BACKGROUND_LIST,
    BackgroundConfig,
    flatColorBg,
} from '@common/shared/form-controls/background-selector/background-list';
import {ControlValueAccessor, NG_VALUE_ACCESSOR} from '@angular/forms';
import {BehaviorSubject} from 'rxjs';
import {RIGHT_POSITION} from '@common/core/ui/overlay-panel/positions/right-position';
import {takeUntil} from 'rxjs/operators';
import {OverlayPanel} from '@common/core/ui/overlay-panel/overlay-panel.service';
import {BackgroundSelectorGradientComponent} from '@common/shared/form-controls/background-selector/background-selector-gradient/background-selector-gradient.component';

type propagateFn = (value: BackgroundConfig) => void;

@Component({
    selector: 'background-selector',
    templateUrl: './background-selector.component.html',
    styleUrls: ['./background-selector.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush,
    providers: [
        {
            provide: NG_VALUE_ACCESSOR,
            useExisting: BackgroundSelectorComponent,
            multi: true,
        },
    ],
})
export class BackgroundSelectorComponent implements ControlValueAccessor {
    backgrounds = BACKGROUND_LIST;
    selectedBg$ = new BehaviorSubject<BackgroundConfig>(null);
    private propagateChange: propagateFn;

    constructor(private overlay: OverlayPanel) {}

    registerOnChange(fn: propagateFn) {
        this.propagateChange = fn;
    }

    writeValue(value: BackgroundConfig) {
        this.selectedBg$.next(value);
    }

    registerOnTouched(fn: any) {}

    onBgClick(bg: BackgroundConfig, e: MouseEvent) {
        if (bg.configId === 'flat') {
            this.setFlatColorBg(this.selectedBg$.value?.color);
            this.openColorPicker(e);
        } else if (bg.configId === 'gradient') {
            this.overlay
                .open(BackgroundSelectorGradientComponent, {
                    origin: new ElementRef(e.target),
                    position: RIGHT_POSITION,
                    data: {active: this.selectedBg$.value},
                })
                .afterClosed()
                .subscribe(value => {
                    if (value) {
                        this.selectBackground(value);
                    }
                });
        } else {
            this.selectBackground(bg);
        }
    }

    selectBackground(bg: BackgroundConfig) {
        // keep background color from "simple color" if
        // it's not specified explicitly for this background
        const oldColor = this.selectedBg$.value?.color;
        const newBg = {...bg};
        if ( ! newBg.color && oldColor) {
            newBg.color = oldColor;
        }
        this.propagateChange(newBg);
        this.selectedBg$.next(newBg);
    }

    private setFlatColorBg(color: string) {
        this.selectBackground({
            ...flatColorBg,
            color,
        });
    }

    private async openColorPicker(e: MouseEvent) {
        const {BeColorPickerModule} = await import(
            '@common/core/ui/color-picker/be-color-picker.module'
        );

        const overlayRef = this.overlay.open(
            BeColorPickerModule.components.panel,
            {
                origin: new ElementRef(e.target),
                position: RIGHT_POSITION,
                data: {color: this.selectedBg$.value?.color},
            }
        );

        overlayRef
            .valueChanged()
            .pipe(takeUntil(overlayRef.afterClosed()))
            .subscribe(color => {
                this.setFlatColorBg(color);
            });
    }
}
