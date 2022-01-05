import {DatatableFilter} from '@common/datatable/datatable-filters/search-input-with-filters/filter-config/datatable-filter';
import {
    CreatedAtFilter,
    UpdatedAtFilter,
} from '@common/datatable/datatable-filters/search-input-with-filters/filter-config/timestamp-filter';

export const GENRE_INDEX_FILTERS: DatatableFilter[] = [
    new CreatedAtFilter({
        description: 'Date genre was created',
    }),
    new UpdatedAtFilter({
        description: 'Date genre was last updated',
    }),
];
