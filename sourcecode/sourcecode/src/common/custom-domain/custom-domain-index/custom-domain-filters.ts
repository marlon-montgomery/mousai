import {
    DatatableFilter,
    FilterControlType,
} from '@common/datatable/datatable-filters/search-input-with-filters/filter-config/datatable-filter';
import {FindUserModalComponent} from '@common/auth/find-user-modal/find-user-modal.component';
import {
    CreatedAtFilter,
    UpdatedAtFilter,
} from '@common/datatable/datatable-filters/search-input-with-filters/filter-config/timestamp-filter';

export const CUSTOM_DOMAIN_FILTERS: DatatableFilter[] = [
    new DatatableFilter({
        type: FilterControlType.Select,
        key: 'global',
        defaultValue: false,
        description: 'Whether domain is set as global or not',
        options: [
            {key: 'No', value: false},
            {key: 'Yes', value: true},
        ],
    }),
    new UpdatedAtFilter({
        description: 'Date the domain was last updated',
    }),
    new CreatedAtFilter({
        description: 'Date the domain was created',
    }),
    new DatatableFilter({
        type: FilterControlType.SelectModel,
        key: 'user_id',
        label: 'User',
        description: 'User domain was created by',
        component: FindUserModalComponent,
    }),
];
