import {Album} from './Album';
import {Genre} from './Genre';
import {Track} from './Track';
import {UserProfile} from './UserProfile';
import {ProfileImage} from './profile-image';
import {UserLink} from './UserLink';

export const ARTIST_MODEL = 'artist';

export interface Artist {
    id: number;
    name: string;
    model_type: 'artist';
    verified?: boolean;
    spotify_id?: string;
    followers_count?: number;
    spotify_popularity?: boolean;
    albums_count?: number;
    image_small?: string;
    updated_at?: string;
    top_tracks?: Track[];
    albums?: Album[];
    similar?: Artist[];
    genres?: Genre[];
    views: number;
    plays: number;
    profile: UserProfile;
    profile_images?: ProfileImage[];
    links?: UserLink[];
}
