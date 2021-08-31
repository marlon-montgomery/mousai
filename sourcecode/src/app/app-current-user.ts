import {Injectable} from '@angular/core';
import {CurrentUser} from '@common/auth/current-user';
import {UserArtist} from './models/App-User';

@Injectable({
    providedIn: 'root',
})
export class AppCurrentUser extends CurrentUser {
    isArtist() {
        return !!this.primaryArtist();
    }

    primaryArtist() {
        return (this.get('artists') || []).find(a => a.role === 'artist');
    }

    canAttachMusicToAnyArtist(): boolean {
        return this.hasPermission('music.update') || !!this.getRestrictionValue('music.create', 'artist_selection');
    }

    artistPlaceholder(): UserArtist {
        return {
            id: 'CURRENT_USER' as any,
            name: this.get('display_name'),
            image_small: this.get('avatar'),
            role: 'artist',
        };
    }
}
