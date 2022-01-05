import {Track} from './Track';
import {Playlist} from './Playlist';
import {Repost} from './repost';
import {UserProfile} from './UserProfile';
import {UserLink} from './UserLink';

export interface UserArtist {
    id: number|'CURRENT_USER';
    name: string;
    image_small: string;
    role: string;
}

declare module '@common/core/types/models/User' {
    interface User {
        uploaded_tracks: Track[];
        followed_users?: this[];
        followers_count?: number;
        followed_users_count?: number;
        followers?: this[];
        model_type: 'user';
        playlists: Playlist[];
        reposts?: Repost[];
        profile?: UserProfile;
        links?: UserLink[];
        unread_notifications_count?: number;
        artists?: UserArtist[];
    }
}
