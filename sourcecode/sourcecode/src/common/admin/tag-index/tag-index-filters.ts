import {
    DatatableFilter,
    FilterControlType,
} from '@common/datatable/datatable-filters/search-input-with-filters/filter-config/datatable-filter';
import {
    CreatedAtFilter,
    UpdatedAtFilter,
} from '@common/datatable/datatable-filters/search-input-with-filters/filter-config/timestamp-filter';

export const TAG_INDEX_FILTERS = (
    types: {name: string; system?: boolean}[]
): DatatableFilter[] => {
    return [
        new DatatableFilter({
            type: FilterControlType.Select,
            key: 'type',
            description: 'Type of the link',
            defaultValue: types[0].name,
            options: types.map(t => {
                return {key: t.name, value: t.name};
            }),
        }),
        new CreatedAtFilter({
            description: 'Date tag was created',
        }),
        new UpdatedAtFilter({
            description: 'Date tag was last updated',
        }),
    ];
};
