import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {DatatableService} from '../../datatable.service';
import {Model} from '@common/core/types/models/model';

@Component({
    selector: 'datatable-header-search-input',
    templateUrl: './datatable-header-search-input.component.html',
    styleUrls: ['./datatable-header-search-input.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush
})
export class DatatableHeaderSearchInputComponent {
    @Input() placeholder: string;
    @Input() hideIcon = false;

    constructor(
        public datatable: DatatableService<Model>,
    ) {}
}
