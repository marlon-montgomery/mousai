import {
    ChangeDetectionStrategy,
    Component,
    HostListener,
    Input,
} from '@angular/core';
import {ControlValueAccessor, NG_VALUE_ACCESSOR} from '@angular/forms';
import {ComponentType} from '@angular/cdk/portal';
import {BehaviorSubject} from 'rxjs';
import { NormalizedModel } from '../../../../../core/types/models/normalized-model';
import { Modal } from '../../../../../core/ui/dialogs/modal.service';

type propagateFn = (value: NormalizedModel) => void;

@Component({
    selector: 'select-model-control',
    templateUrl: './select-model-control.component.html',
    styleUrls: ['./select-model-control.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush,
    providers: [
        {
            provide: NG_VALUE_ACCESSOR,
            useExisting: SelectModelControlComponent,
            multi: true,
        },
    ],
})
export class SelectModelControlComponent implements ControlValueAccessor {
    @Input() component: ComponentType<any>;
    private propagateChange: propagateFn;
    value$ = new BehaviorSubject<NormalizedModel>(null);

    constructor(private dialog: Modal) {}

    registerOnChange(fn: propagateFn): void {
        this.propagateChange = fn;
    }

    registerOnTouched(fn: any): void {
    }

    writeValue(value: NormalizedModel) {
        this.value$.next(value);
    }

    @HostListener('click')
    onClick() {
        this.openSelectModelDialog();
    }

    public openSelectModelDialog() {
        this.dialog.open(this.component, {normalizeValue: true})
            .afterClosed()
            .subscribe((model: NormalizedModel) => {
                if (model) {
                    this.value$.next(model);
                    this.propagateChange(model);
                }
            });
    }
}
