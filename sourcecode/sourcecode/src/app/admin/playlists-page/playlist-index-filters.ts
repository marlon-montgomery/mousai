import {
    ALL_PRIMITIVE_OPERATORS,
    DatatableFilter,
    FilterControlType, FilterOperator
} from '@common/datatable/datatable-filters/search-input-with-filters/filter-config/datatable-filter';
import {
    CreatedAtFilter,
    UpdatedAtFilter,
} from '@common/datatable/datatable-filters/search-input-with-filters/filter-config/timestamp-filter';

export const PLAYLIST_INDEX_FILTERS: DatatableFilter[] = [
    new DatatableFilter({
        type: FilterControlType.Select,
        key: 'public',
        label: 'Status',
        defaultValue: true,
        description: 'Whether playlist is public',
        options: [
            {
                key: 'Public',
                value: true,
            },
            {
                key: 'Private',
                value: false,
            },
        ],
    }),
    new DatatableFilter({
        type: FilterControlType.Select,
        key: 'collaborative',
        label: 'Collaboration',
        defaultValue: true,
        description: 'Whether playlist is collaborative',
        options: [
            {
                key: 'Enabled',
                value: true,
            },
            {
                key: 'Disabled',
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
        description: 'Total number of plays for the playlist',
        operators: ALL_PRIMITIVE_OPERATORS,
        defaultOperator: FilterOperator.gte,
    }),
    new CreatedAtFilter({
        description: 'Date playlist was created',
    }),
    new UpdatedAtFilter({
        description: 'Date playlist was last updated',
    }),
];
