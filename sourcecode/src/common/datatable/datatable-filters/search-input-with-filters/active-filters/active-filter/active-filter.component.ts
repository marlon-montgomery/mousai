import {
    AfterViewInit,
    ChangeDetectionStrategy,
    Component,
    ElementRef,
    HostBinding,
    Input,
    ViewChild,
} from '@angular/core';
import {FormGroup} from '@angular/forms';
import {
    DatatableFilter,
    FilterValue,
} from '../../filter-config/datatable-filter';
import * as deepequal from 'fast-deep-equal';
import {SelectModelControlComponent} from '../select-model-control/select-model-control.component';

@Component({
    selector: 'active-filter',
    templateUrl: './active-filter.component.html',
    styleUrls: ['./active-filter.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush,
    host: {
        tabIndex: '0',
        role: 'button',
    },
})
export class ActiveFilterComponent implements AfterViewInit {
    @HostBinding('attr.data-control-key') @Input() key: string;
    @Input() compact: boolean;
    @Input() form: FormGroup;
    @Input() filter: DatatableFilter;
    @ViewChild('valueInput') valueInput:
        | SelectModelControlComponent
        | ElementRef<HTMLSelectElement | HTMLInputElement>;

    constructor(public el: ElementRef<HTMLElement>) {}

    ngAfterViewInit() {
        this.resizeInput();
    }

    focus() {
        this.el.nativeElement.focus();
    }

    focusValueInput() {
        if (this.valueInput instanceof SelectModelControlComponent) {
            this.valueInput.openSelectModelDialog();
        } else if (this.valueInput?.nativeElement) {
            this.valueInput.nativeElement.focus();
        }
    }

    resizeInput() {
        const el = (this.valueInput as ElementRef)?.nativeElement;
        if (el?.nodeName === 'SELECT') {
            const select = el as HTMLSelectElement;
            if (select.selectedIndex > -1) {
                const valueLength =
                    select.options[select.selectedIndex].label.length;
                select.style.width = `${valueLength + 5}ch`;
            }
        } else if (el?.nodeName === 'INPUT' && el.type !== 'date') {
            const input = el as HTMLInputElement;
            input.style.width = `${input.value.length + 7}ch`;
        }
    }

    compareFilterValueFn = (val1: FilterValue, val2: FilterValue) => {
        return deepequal(val1, val2);
    }
}
