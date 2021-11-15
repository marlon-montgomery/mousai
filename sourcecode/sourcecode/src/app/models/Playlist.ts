import {Track} from './Track';
import {User} from '@common/core/types/models/User';

export const PLAYLIST_MODEL = 'playlist';

export interface Playlist {
    id: number;
    name: string;
    public: boolean;
    collaborative: boolean;
    image: string;
    description: string;
    created_at: string;
    updated_at: string;
    owner_id: number;
    owner?: User;
    editors?: User[];
    tracks_count?: number;
    tracks?: Track[];
    model_type: 'playlist';
    views: number;
}
