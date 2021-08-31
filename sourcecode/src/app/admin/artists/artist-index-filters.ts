import {
    ALL_PRIMITIVE_OPERATORS,
    DatatableFilter,
    FilterControlType, FilterOperator,
} from '@common/datatable/datatable-filters/search-input-with-filters/filter-config/datatable-filter';
import {
    CreatedAtFilter,
    UpdatedAtFilter,
} from '@common/datatable/datatable-filters/search-input-with-filters/filter-config/timestamp-filter';
import {FindUserModalComponent} from '@common/auth/find-user-modal/find-user-modal.component';

export const ARTIST_INDEX_FILTERS: DatatableFilter[] = [
    new DatatableFilter({
        type: FilterControlType.Select,
        key: 'verified',
        label: 'Status',
        defaultValue: false,
        description: 'Whether artist is verified',
        options: [
            {
                key: 'Verified',
                value: true,
            },
            {
                key: 'Not Verified',
                value: false,
            },
        ],
    }),

    new DatatableFilter({
        type: FilterControlType.Input,
        inputType: 'number',
        key: 'plays',
        label: 'Plays',
        defaultValue: 100,
        description: 'Total number of plays for all artist tracks',
        operators: ALL_PRIMITIVE_OPERATORS,
        defaultOperator: FilterOperator.gte,
    }),

    new CreatedAtFilter({
        description: 'Date artist was created',
    }),
    new UpdatedAtFilter({
        description: 'Date artist was last updated',
    }),
];
