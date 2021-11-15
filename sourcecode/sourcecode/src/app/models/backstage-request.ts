import {User} from '@common/core/types/models/User';
import {ExternalSocialProfile} from '@common/auth/external-social-profile';
import {FileEntry} from '@common/uploads/types/file-entry';
import {Artist} from './Artist';

export interface BackstageRequest {
    id: number;
    artist_name: string;
    artist_id: string;
    type: string;
    user_id: number;
    user: User;
    artist?: Artist;
    created_at: string;
    status: string;
    data: {
        socialProfiles: {[key: string]: ExternalSocialProfile},
        first_name?: string;
        last_name?: string;
        image?: string;
        company?: string;
        role?: string;
        passportScanEntryId?: number;
        passportScanEntry?: FileEntry;
    };
}
