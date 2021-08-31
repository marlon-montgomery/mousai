import { FilterOperator } from '@common/datatable/datatable-filters/search-input-with-filters/filter-config/datatable-filter';

export const FILTER_OPERATOR_NAMES: {[op in FilterOperator]: string} = {
  '=': 'is',
  '!=': 'is not',
  '>': 'is greater than',
  '>=': 'is greater than or equal to',
  '<': 'is less than',
  '<=': 'is less than or equal to',
  has: 'Include',
  doesntHave: 'Do not include',
};

