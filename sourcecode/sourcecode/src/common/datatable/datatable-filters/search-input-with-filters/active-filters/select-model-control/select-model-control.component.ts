import {ChangeDetectionStrategy, Component, HostListener, Input} from '@angular/core';
import {ControlValueAccessor, NG_VALUE_ACCESSOR} from '@angular/forms';
import {BehaviorSubject} from 'rxjs';
import {NormalizedModel} from '@common/core/types/models/normalized-model';
import {Modal} from '@common/core/ui/dialogs/modal.service';
import {DatatableFilter} from '@common/datatable/datatable-filters/search-input-with-filters/filter-config/datatable-filter';

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
    @Input() filter: DatatableFilter;
    private propagateChange: propagateFn;
    value$ = new BehaviorSubject<NormalizedModel>(null);

    constructor(private dialog: Modal) {}

    registerOnChange(fn: propagateFn): void {
        this.propagateChange = fn;
    }

    registerOnTouched(fn: any): void {}

    writeValue(value: NormalizedModel) {
        this.value$.next(value);
    }

    @HostListener('click')
    onClick() {
        this.openSelectModelDialog();
    }

    openSelectModelDialog() {
        this.dialog
            .open(this.filter.component, this.filter.componentData)
            .afterClosed()
            .subscribe((model: NormalizedModel) => {
                if (model) {
                    this.value$.next(model);
                    this.propagateChange(model);
                }
            });
    }
}
