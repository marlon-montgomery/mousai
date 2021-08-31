import {
    ChangeDetectionStrategy,
    ChangeDetectorRef,
    Component,
    ElementRef,
    Input,
    QueryList,
    ViewChildren,
} from '@angular/core';
import {FormBuilder, FormGroup} from '@angular/forms';
import {DatatableFilter} from '../filter-config/datatable-filter';
import {randomString} from '../../../../core/utils/random-string';
import {ActiveFilterComponent} from './active-filter/active-filter.component';

interface AddFilterOptions {
    value?: any;
    operator?: string;
    focus?: boolean;
}

@Component({
    selector: 'active-filters',
    templateUrl: './active-filters.component.html',
    styleUrls: ['./active-filters.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ActiveFiltersComponent {
    @Input() compact: boolean;
    @Input() form: FormGroup;
    @Input() config: DatatableFilter;
    @ViewChildren(ActiveFilterComponent)
    filters: QueryList<ActiveFilterComponent>;

    constructor(
        private fb: FormBuilder,
        private cd: ChangeDetectorRef,
        public el: ElementRef<HTMLElement>
    ) {}

    add(config: DatatableFilter, opts: AddFilterOptions = {}) {
        const control = this.fb.group({
            key: config.key,
            value: opts.value !== undefined ? opts.value : config.defaultValue,
            operator: opts.operator || config.defaultOperator,
        });
        const key = Object.keys(this.form.controls).length + randomString(8);
        this.form.addControl(key, control);
        this.cd.detectChanges();
        if (opts.focus) {
            this.filters.last.focusValueInput();
        }
    }

    removeByKey(key: string) {
        this.form.removeControl(key);
        this.cd.markForCheck();
    }

    removeCurrentlyFocused() {
        const activeEl = document.activeElement as HTMLElement;
        this.removeByKey(activeEl.dataset.controlKey);
    }

    anyFocused(): boolean {
        return document.activeElement.nodeName === 'ACTIVE-FILTER';
    }

    focusLast() {
        if (this.filters.last) {
            this.filters.last.focus();
        }
    }

    focusPrevious() {
        const i = this.getFocusedIndex();
        const previous = this.filters.get(i - 1);
        if (previous) {
            previous.focus();
        }
    }

    getByIndex(index: number) {
        return this.filters.get(index);
    }

    lastIsFocused() {
        const i = this.getFocusedIndex();
        return i === this.filters.length - 1;
    }

    getFocusedIndex(): number {
        const i = (document.activeElement as HTMLElement).dataset.index;
        return i ? parseInt(i) : null;
    }
}
