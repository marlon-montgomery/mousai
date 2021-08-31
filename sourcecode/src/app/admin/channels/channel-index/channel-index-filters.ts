import {
    DatatableFilter,
    FilterControlType,
} from '@common/datatable/datatable-filters/search-input-with-filters/filter-config/datatable-filter';
import {
    CreatedAtFilter,
    UpdatedAtFilter,
} from '@common/datatable/datatable-filters/search-input-with-filters/filter-config/timestamp-filter';
import {FindUserModalComponent} from '@common/auth/find-user-modal/find-user-modal.component';

export const CHANNEL_INDEX_FILTERS: DatatableFilter[] = [
    new DatatableFilter({
        type: FilterControlType.SelectModel,
        key: 'user_id',
        label: 'User',
        description: 'User channel was created by',
        component: FindUserModalComponent,
    }),

    new CreatedAtFilter({
        description: 'Date channel was created',
    }),
    new UpdatedAtFilter({
        description: 'Date channel was last updated',
    }),
];
