import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {DatatableFilter} from '@common/datatable/datatable-filters/search-input-with-filters/filter-config/datatable-filter';

@Component({
    selector: 'datatable-page-header',
    templateUrl: './datatable-page-header.component.html',
    styleUrls: ['./datatable-page-header.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class DatatablePageHeaderComponent {
    @Input() filters: DatatableFilter[];
    @Input() pluralName: string;
}
