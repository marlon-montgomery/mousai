import {
    DatatableFilter,
    FilterControlType,
} from '@common/datatable/datatable-filters/search-input-with-filters/filter-config/datatable-filter';
import {
    CreatedAtFilter,
    UpdatedAtFilter,
} from '@common/datatable/datatable-filters/search-input-with-filters/filter-config/timestamp-filter';
import {FindUserModalComponent} from '@common/auth/find-user-modal/find-user-modal.component';

export const COMMENT_INDEX_FILTERS: DatatableFilter[] = [
    new DatatableFilter({
        type: FilterControlType.Select,
        key: 'deleted',
        label: 'Type',
        defaultValue: false,
        description: 'Whether comment is active or deleted.',
        options: [
            {
                key: 'Active',
                value: false,
            },
            {
                key: 'Deleted',
                value: true,
            },
        ],
    }),
    new DatatableFilter({
        type: FilterControlType.SelectModel,
        key: 'user_id',
        label: 'User',
        description: 'User comment was created by',
        component: FindUserModalComponent,
    }),

    new CreatedAtFilter({
        description: 'Date comment was created',
    }),
    new UpdatedAtFilter({
        description: 'Date comment was last updated',
    }),
];
