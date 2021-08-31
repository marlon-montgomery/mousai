import {
    DatatableFilter,
    FilterControlType,
    FilterOperator,
} from '@common/datatable/datatable-filters/search-input-with-filters/filter-config/datatable-filter';
import {
    CreatedAtFilter,
    UpdatedAtFilter,
} from '@common/datatable/datatable-filters/search-input-with-filters/filter-config/timestamp-filter';

export const USER_INDEX_FILTERS: DatatableFilter[] = [
    new DatatableFilter({
        type: FilterControlType.Select,
        key: 'email_verified_at',
        label: 'Email',
        description: 'Email verification status',
        defaultValue: {value: null, operator: FilterOperator.ne},
        defaultOperator: FilterOperator.ne,
        options: [
            {
                key: 'Confirmed',
                value: {value: null, operator: FilterOperator.ne},
            },
            {
                key: 'Not Confirmed',
                value: {value: null, operator: FilterOperator.eq},
            },
        ],
    }),
    new CreatedAtFilter({
        description: 'Date user registered or was created',
    }),
    new UpdatedAtFilter({
        description: 'Date user was last updated',
    }),
    new DatatableFilter({
        type: FilterControlType.Select,
        key: 'subscriptions',
        label: 'Subscribed',
        description: 'Whether user is subscribed or not',
        defaultValue: {value: '*', operator: FilterOperator.has},
        defaultOperator: FilterOperator.ne,
        options: [
            {key: 'Yes', value: {value: '*', operator: FilterOperator.has}},
            {
                key: 'No',
                value: {value: '*', operator: FilterOperator.doesntHave},
            },
        ],
    }),
];
