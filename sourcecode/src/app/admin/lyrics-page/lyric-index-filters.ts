import {DatatableFilter} from '@common/datatable/datatable-filters/search-input-with-filters/filter-config/datatable-filter';
import {
    CreatedAtFilter,
    UpdatedAtFilter,
} from '@common/datatable/datatable-filters/search-input-with-filters/filter-config/timestamp-filter';

export const LYRIC_INDEX_FILTERS: DatatableFilter[] = [
    new CreatedAtFilter({
        description: 'Date lyric was created',
    }),
    new UpdatedAtFilter({
        description: 'Date lyric was last updated',
    }),
];
