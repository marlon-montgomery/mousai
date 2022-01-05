import { DatatableFilter, FilterControlType } from '@common/datatable/datatable-filters/search-input-with-filters/filter-config/datatable-filter';
import { CreatedAtFilter, UpdatedAtFilter } from '@common/datatable/datatable-filters/search-input-with-filters/filter-config/timestamp-filter';

export const PLAN_INDEX_FILTERS: DatatableFilter[] = [
  new DatatableFilter({
    type: FilterControlType.Select,
    key: 'parent_id',
    label: 'Type',
    description: 'Whether plan is a child or not',
    defaultValue: null,
    options: [
      {key: 'Any', value: null},
      {key: 'Child', value: {value: null, operator: '!='}},
      {key: 'Parent', value: {value: null, operator: '='}},
    ],
  }),
  new DatatableFilter({
    type: FilterControlType.Select,
    key: 'currency',
    description: 'Currency assigned to the plan',
    defaultValue: 'USD',
    options: [
      {key: 'USD', value: 'USD'},
      {key: 'EUR', value: 'EUR'},
      {key: 'Pound Sterling', value: 'GBP'},
      {key: 'Canadian Dollar', value: 'CAD'},
    ],
  }),
  new DatatableFilter({
    type: FilterControlType.Select,
    key: 'interval',
    description: 'Currency assigned to the plan',
    defaultValue: 'USD',
    options: [
      {key: 'Day', value: 'day'},
      {key: 'Week', value: 'week'},
      {key: 'Month', value: 'month'},
      {key: 'Year', value: 'yea'},
    ],
  }),
  new CreatedAtFilter({
    description: 'Date plan was created',
  }),
  new UpdatedAtFilter({
    description: 'Date plan was last updated',
  }),
];
