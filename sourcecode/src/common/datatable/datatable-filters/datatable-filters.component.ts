import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {DatatableService} from '@common/datatable/datatable.service';
import {Model} from '@common/core/types/models/model';
import {DatatableFilter} from './search-input-with-filters/filter-config/datatable-filter';

@Component({
    selector: 'datatable-filters',
    templateUrl: './datatable-filters.component.html',
    styleUrls: ['./datatable-filters.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class DatatableFiltersComponent {
    @Input() pluralName: string;
    @Input() filters: DatatableFilter[];

    constructor(public datable: DatatableService<Model>) {}

    onFilterChange(filters: string) {
        this.datable.filters$.next({
            filters,
        });
    }
}
