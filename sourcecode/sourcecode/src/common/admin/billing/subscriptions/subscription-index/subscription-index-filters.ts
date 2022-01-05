import {
    DatatableFilter,
    FilterControlType,
    FilterOperator,
} from '@common/datatable/datatable-filters/search-input-with-filters/filter-config/datatable-filter';
import { CreatedAtFilter, UpdatedAtFilter } from '@common/datatable/datatable-filters/search-input-with-filters/filter-config/timestamp-filter';

export const SUBSCRIPTION_INDEX_FILTERS: DatatableFilter[] = [
    new DatatableFilter({
        type: FilterControlType.Select,
        key: 'ends_at',
        label: 'Status',
        description: 'Whether subscription is active or cancelled',
        defaultValue: {value: null, operator: FilterOperator.eq},
        options: [
            {key: 'Active', value: {value: null, operator: FilterOperator.eq}},
            {key: 'Cancelled', value: {value: null, operator: FilterOperator.ne}},
        ],
    }),
    new DatatableFilter({
        type: FilterControlType.Select,
        key: 'gateway_name',
        label: 'Gateway',
        description: 'With which payment provider was subscription created',
        defaultValue: 'stripe',
        options: [
            {key: 'Stripe', value: 'stripe'},
            {key: 'Paypal', value: 'paypal'},
            {key: 'None', value: null},
        ],
    }),
  new CreatedAtFilter({
    description: 'Date subscription was created',
  }),
  new UpdatedAtFilter({
    description: 'Date subscription was last updated',
  }),
];
