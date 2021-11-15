import {
    ALL_PRIMITIVE_OPERATORS,
    DatatableFilter,
    FilterControlType, FilterOperator,
} from '@common/datatable/datatable-filters/search-input-with-filters/filter-config/datatable-filter';
import {
    CreatedAtFilter, TimestampFilter,
    UpdatedAtFilter,
} from '@common/datatable/datatable-filters/search-input-with-filters/filter-config/timestamp-filter';
import {FindUserModalComponent} from '@common/auth/find-user-modal/find-user-modal.component';

export const ALBUM_INDEX_FILTERS: DatatableFilter[] = [
    new DatatableFilter({
        type: FilterControlType.Select,
        key: 'image',
        label: 'Artwork',
        defaultValue: false,
        description: 'Whether album has any artwork',
        options: [
            {
                key: 'Has artwork',
                value: {operator: FilterOperator.ne, value: null},
            },
            {
                key: 'Does not have artwork',
                value: null,
            },
        ],
    }),

    new DatatableFilter({
        type: FilterControlType.Input,
        inputType: 'number',
        key: 'plays',
        label: 'Plays',
        defaultValue: 100,
        description: 'Total number of plays for all album tracks',
        operators: ALL_PRIMITIVE_OPERATORS,
        defaultOperator: FilterOperator.gte,
    }),

    new TimestampFilter({
        key: 'release_date',
        label: 'Release Date',
        description: 'Release date for the album',
    }),
    new CreatedAtFilter({
        description: 'Date album was created',
    }),
    new UpdatedAtFilter({
        description: 'Date album was last updated',
    }),
];
