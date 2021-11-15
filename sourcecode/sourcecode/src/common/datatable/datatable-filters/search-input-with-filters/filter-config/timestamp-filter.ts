import {
    ALL_PRIMITIVE_OPERATORS,
    DatatableFilter,
    FilterControlType,
    FilterOperator,
} from './datatable-filter';

export class TimestampFilter extends DatatableFilter {
    type = FilterControlType.DatePicker;
    operators = ALL_PRIMITIVE_OPERATORS;
    defaultOperator = FilterOperator.lte;
    defaultValue = new Date().toISOString().split('T')[0];
}

export class CreatedAtFilter extends TimestampFilter {
  key = 'created_at';
  label = 'Created At';
}

export class UpdatedAtFilter extends TimestampFilter {
  key = 'updated_at';
  label = 'Updated At';
}


