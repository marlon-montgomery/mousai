import {
    DatatableFilter,
    FilterControlType,
} from '@common/datatable/datatable-filters/search-input-with-filters/filter-config/datatable-filter';
import {FindUserModalComponent} from '@common/auth/find-user-modal/find-user-modal.component';
import {
    CreatedAtFilter,
    UpdatedAtFilter,
} from '@common/datatable/datatable-filters/search-input-with-filters/filter-config/timestamp-filter';

export const CUSTOM_PAGE_FILTERS: DatatableFilter[] = [
    new UpdatedAtFilter({
        description: 'Date the page was last updated',
    }),
    new CreatedAtFilter({
        description: 'Date the page was created',
    }),
    new DatatableFilter({
        type: FilterControlType.SelectModel,
        key: 'user_id',
        label: 'User',
        description: 'User page was created by',
        component: FindUserModalComponent,
    }),
];
