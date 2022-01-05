import {
    DatatableFilter,
    FilterControlType,
} from '@common/datatable/datatable-filters/search-input-with-filters/filter-config/datatable-filter';
import { CreatedAtFilter, UpdatedAtFilter } from '@common/datatable/datatable-filters/search-input-with-filters/filter-config/timestamp-filter';
import { FindUserModalComponent } from '@common/auth/find-user-modal/find-user-modal.component';

export const FILE_ENTRY_TYPE_FILTER = new DatatableFilter({
    type: FilterControlType.Select,
    key: 'type',
    description: 'Type of the file',
    defaultValue: 'text',
    options: [
        {key: 'Text', value: 'text'},
        {key: 'Audio', value: 'audio'},
        {key: 'Video', value: 'video'},
        {key: 'Image', value: 'image'},
        {key: 'PDF', value: 'pdf'},
        {key: 'Spreadsheet', value: 'spreadsheet'},
        {key: 'Word Document', value: 'word'},
        {key: 'Photoshop', value: 'photoshop'},
        {key: 'Archive', value: 'archive'},
        {key: 'Folder', value: 'folder'},
    ],
});

export const FILE_ENTRY_INDEX_FILTERS: DatatableFilter[] = [
    FILE_ENTRY_TYPE_FILTER,
    new DatatableFilter({
        type: FilterControlType.Select,
        key: 'public',
        label: 'status',
        defaultValue: false,
        description: 'Whether file is publicly accessible',
        options: [
            {key: 'Private', value: false},
            {key: 'Public', value: true},
        ],
    }),
    new CreatedAtFilter({
        description: 'Date file was uploaded',
    }),
    new UpdatedAtFilter({
        description: 'Date file was last changed',
    }),
    new DatatableFilter({
        type: FilterControlType.SelectModel,
        key: 'owner_id',
        label: 'Uploader',
        description: 'User that this file was uploaded by',
        component: FindUserModalComponent,
    }),
];
