import {Injectable} from '@angular/core';
import {Router, Resolve, ActivatedRouteSnapshot} from '@angular/router';
import {WebPlayerState} from '../../../web-player-state.service';
import {Playlist} from '../../../../models/Playlist';
import {Playlists} from '../../../playlists/playlists.service';
import {CurrentUser} from '@common/auth/current-user';
import {UserProfileService} from '../../user-profile.service';

@Injectable({
    providedIn: 'root'
})
export class LibraryPlaylistsResolver implements Resolve<Playlist[]> {
    constructor(
        private userProfile: UserProfileService,
        private router: Router,
        private state: WebPlayerState,
        private user: CurrentUser
    ) {}

    resolve(route: ActivatedRouteSnapshot): Promise<Playlist[]> {
        this.state.loading = true;
        return this.userProfile.playlists(this.user.get('id'), {page: 1}).toPromise().then(response => {
            this.state.loading = false;

            if (response) {
                return response;
            } else {
                this.router.navigate(['/library']);
                return null;
            }
        }).catch(() => {
            this.state.loading = false;
            this.router.navigate(['/library']);
        }) as any;
    }
}
