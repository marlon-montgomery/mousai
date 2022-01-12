import {Track} from './Track';
import {Tag} from '@common/core/types/models/Tag';
import {Genre} from './Genre';
import {Artist} from './Artist';

export const ALBUM_MODEL = 'album';

export interface Album {
    id: number;
    name: string;
    model_type: 'album';
    release_date?: string;
    spotify_id?: string;
    image?: string;
    artists?: Artist[];
    reposts_count?: number;
    likes_count?: number;
    plays?: number;
    views: number;
    description?: string;
    tracks?: Track[];
    tags?: Tag[];
    genres?: Genre[];
    created_at?: string;
    owner_id?: number;
    comments_count?: number;
    tracks_count?: number;
    updated_at: string;
}
