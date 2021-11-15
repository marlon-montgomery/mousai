import {Track} from './Track';
import {Album} from './Album';

export interface Repost {
    id: number;
    track_id: number;
    user_id: number;
    created_at: string;
    repostable?: Track|Album;
}
