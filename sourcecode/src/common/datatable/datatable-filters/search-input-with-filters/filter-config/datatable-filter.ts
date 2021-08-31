import {ComponentType} from '@angular/cdk/portal';
import {NormalizedModel} from '../../../../core/types/models/normalized-model';

export class DatatableFilter {
    defaultValue: FilterValue | '';
    defaultOperator: FilterOperator;
    key: string;
    label: string;
    description: string;
    required: boolean;
    order: number;
    type: FilterControlType;
    inputType: 'string' | 'number';
    options: {key: string; value: string | boolean | object | number}[];
    operators: FilterOperator[] | undefined;
    component?: ComponentType<any>;

    constructor(options: DatatableFilterOptions) {
        this.defaultValue =
            options.defaultValue !== undefined ? options.defaultValue : '';
        this.key = options.key || '';
        this.label = options.label || options.key || '';
        this.description = options.description || '';
        this.defaultOperator = options.defaultOperator || FilterOperator.eq;
        this.required = !!options.required;
        this.order = options.order === undefined ? 1 : options.order;
        this.type = options.type || FilterControlType.Select;
        this.inputType = options.inputType;
        this.options = options.options || [];
        this.operators = options.operators;
        this.component = options.component;
    }
}

export interface DatatableFilterOptions {
    defaultValue?: FilterValue | '';
    key?: string;
    label?: string;
    description: string;
    component?: ComponentType<any>;
    defaultOperator?: FilterOperator;
    required?: boolean;
    order?: number;
    type?: FilterControlType;
    inputType?: 'string' | 'number';
    options?: {key: string; value: string | boolean | object | number}[];
    operators?: FilterOperator[];
}

export enum FilterControlType {
    Select = 'select',
    DatePicker = 'datePicker',
    SelectModel = 'selectModel',
    Input = 'input',
    StaticValue = 'staticValue',
}

export enum FilterOperator {
    eq = '=',
    ne = '!=',
    gt = '>',
    gte = '>=',
    lt = '<',
    lte = '<=',
    has = 'has',
    doesntHave = 'doesntHave',
}

export type FilterValueWithOperator = {
    value: FilterValue;
    operator: FilterOperator;
};
export const ALL_PRIMITIVE_OPERATORS = [
    FilterOperator.eq,
    FilterOperator.ne,
    FilterOperator.gt,
    FilterOperator.gte,
    FilterOperator.lt,
    FilterOperator.lte,
];

export type FilterValue =
    | string
    | number
    | boolean
    | NormalizedModel
    | FilterValueWithOperator
    | null;
