import {ChannelContentItem} from './channel-content-item';
import {PaginationResponse} from '@common/core/types/pagination/pagination-response';

export const CHANNEL_MODEL = 'channel';

export interface Channel {
    id: number;
    name: string;
    slug: string;
    config: {
        carouselWhenNested: boolean;
        autoUpdateMethod?: string;
        disablePagination?: boolean;
        disablePlayback?: boolean;
        connectToGenreViaUrl?: boolean;
        contentModel?: string;
        layout: string;
        hideTitle?: boolean;
        lockSlug?: boolean;
        actions?: {tooltip: string; icon: string, route: string}[];
    };
    model_type: 'channel';
    updated_at?: string;
    content?: PaginationResponse<ChannelContentItem>;
}
