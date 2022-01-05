import {Album} from './Album';
import {Lyric} from './Lyric';
import {Tag} from '@common/core/types/models/Tag';
import {Genre} from './Genre';
import {Artist} from './Artist';

export const TRACK_MODEL = 'track';

export interface Track {
    id: number;
    name: string;
    duration?: number;
    artists?: Artist[];
    youtube_id?: string;
    plays?: number;
    popularity?: number;
    url?: string;
    image?: string;
    lyric?: Lyric;
    album?: Album;
    description?: string;
    tags: Tag[];
    genres: Genre[];
    likes_count?: number;
    reposts_count?: number;
    comments_count?: number;
    updated_at?: string;
    created_at?: string;
    model_type: 'track';
}

