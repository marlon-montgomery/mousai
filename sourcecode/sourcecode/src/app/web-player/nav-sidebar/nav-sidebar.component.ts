import {Component, OnInit, ViewEncapsulation} from '@angular/core';
import {Settings} from '@common/core/config/settings.service';
import {SearchSlideoutPanel} from '../search/search-slideout-panel/search-slideout-panel.service';
import {Player} from '../player/player.service';
import {WebPlayerUrls} from '../web-player-urls.service';
import {UserPlaylists} from '../playlists/user-playlists.service';
import {Modal} from '@common/core/ui/dialogs/modal.service';
import {CrupdatePlaylistModalComponent} from '../playlists/crupdate-playlist-modal/crupdate-playlist-modal.component';
import {Router} from '@angular/router';
import {AuthService} from '@common/auth/auth.service';
import {ThemeService} from '@common/core/theme.service';
import {BehaviorSubject} from 'rxjs';
import {AppCurrentUser} from '../../app-current-user';

@Component({
    selector: 'nav-sidebar',
    templateUrl: './nav-sidebar.component.html',
    styleUrls: ['./nav-sidebar.component.scss'],
    encapsulation: ViewEncapsulation.None,
})
export class NavSidebarComponent implements OnInit {
    public unreadCount$ = new BehaviorSubject<number>(0);
    public profileLink: (string|number)[];

    constructor(
        public settings: Settings,
        public searchPanel: SearchSlideoutPanel,
        public currentUser: AppCurrentUser,
        public player: Player,
        public urls: WebPlayerUrls,
        public auth: AuthService,
        public playlists: UserPlaylists,
        private modal: Modal,
        private router: Router,
        public theme: ThemeService,
    ) {}

    ngOnInit() {
        this.unreadCount$.next(this.currentUser.get('unread_notifications_count'));
        if (this.currentUser.isArtist()) {
            this.profileLink = this.urls.artist(this.currentUser.primaryArtist());
        } else {
            this.profileLink = this.urls.user(this.currentUser.getModel());
        }
    }

    public openNewPlaylistModal() {
        if ( ! this.currentUser.isLoggedIn()) {
            return this.router.navigate(['/login']);
        }

        this.modal.open(CrupdatePlaylistModalComponent)
            .afterClosed()
            .subscribe(playlist => {
                if ( ! playlist) return;
                this.playlists.add(playlist);
                this.router.navigate(this.urls.playlist(playlist));
            });
    }
}
