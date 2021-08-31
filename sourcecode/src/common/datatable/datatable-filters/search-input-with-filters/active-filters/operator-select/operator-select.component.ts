import {
    AfterViewInit,
    ChangeDetectionStrategy,
    Component,
    ElementRef,
    Input,
    ViewChild,
} from '@angular/core';
import {FormGroup} from '@angular/forms';
import {FilterOperator} from '../../filter-config/datatable-filter';

@Component({
    selector: 'operator-select',
    templateUrl: './operator-select.component.html',
    styleUrls: ['./operator-select.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class OperatorSelectComponent implements AfterViewInit {
    @Input() compact: boolean;
    @Input() formGroup: FormGroup;
    @Input() operators: FilterOperator[];
    @ViewChild('select') select: ElementRef<HTMLSelectElement>;

    ngAfterViewInit() {
        this.resizeSelect();
    }

    resizeSelect() {
        const select = this.select?.nativeElement;
        if (select && select.selectedIndex > -1) {
            const valueLength =
                select.options[select.selectedIndex].label.length;
            select.style.width = `${valueLength + 5}ch`;
        }
    }
}
