import {
    ALL_PRIMITIVE_OPERATORS,
    DatatableFilter,
    FilterControlType, FilterOperator
} from '@common/datatable/datatable-filters/search-input-with-filters/filter-config/datatable-filter';
import {
    CreatedAtFilter,
    UpdatedAtFilter,
} from '@common/datatable/datatable-filters/search-input-with-filters/filter-config/timestamp-filter';
import {FindUserModalComponent} from '@common/auth/find-user-modal/find-user-modal.component';

export const BACKSTAGE_REQUEST_INDEX_FILTERS: DatatableFilter[] = [
    new DatatableFilter({
        type: FilterControlType.Select,
        key: 'type',
        label: 'Type',
        defaultValue: 'become-artist',
        description: 'Type of the request',
        options: [
            {
                key: 'Become Artist',
                value: 'become-artist',
            },
            {
                key: 'Verify Artist',
                value: 'verify-artist',
            },
            {
                key: 'Claim Artist',
                value: 'claim-artist',
            },
        ],
    }),
    new DatatableFilter({
        type: FilterControlType.Select,
        key: 'status',
        label: 'Status',
        defaultValue: 'pending',
        description: 'Status of the request',
        options: [
            {
                key: 'Pending',
                value: 'pending',
            },
            {
                key: 'Approved',
                value: 'approved',
            },
            {
                key: 'Denied',
                value: 'denied',
            },
        ],
    }),

    new DatatableFilter({
        type: FilterControlType.SelectModel,
        key: 'user_id',
        label: 'Requester',
        description: 'User request was submitted by',
        component: FindUserModalComponent,
    }),


    new CreatedAtFilter({
        description: 'Date request was created',
    }),
    new UpdatedAtFilter({
        description: 'Date request was last updated',
    }),
];
