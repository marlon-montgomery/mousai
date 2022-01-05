import {ChangeDetectorRef, Component, OnDestroy, OnInit} from '@angular/core';
import {ActivatedRoute, NavigationEnd, Router} from '@angular/router';
import {Settings} from '@common/core/config/settings.service';
import {WebPlayerUrls} from '../../web-player-urls.service';
import {BehaviorSubject, Subscription} from 'rxjs';
import {filter, map} from 'rxjs/operators';
import {User} from '@common/core/types/models/User';
import {CurrentUser} from '@common/auth/current-user';
import {Users} from '@common/auth/users.service';
import {Modal} from '@common/core/ui/dialogs/modal.service';
import {EditUserProfileModalComponent} from './edit-user-profile-modal/edit-user-profile-modal.component';
import {UploadQueueService} from '@common/uploads/upload-queue/upload-queue.service';
import {Toast} from '@common/core/ui/toast.service';
import {DomSanitizer} from '@angular/platform-browser';
import {getFaviconFromUrl} from '@common/core/utils/get-favicon-from-url';
import {UserProfileService} from '../user-profile.service';
import {RepostsService} from '../../shared/reposts.service';
import {PaginatedBackendResponse} from '@common/core/types/pagination/paginated-backend-response';
import {Album} from '../../../models/Album';
import {Track} from '../../../models/Track';

@Component({
    selector: 'user-profile-page',
    templateUrl: './user-profile-page.component.html',
    styleUrls: ['./user-profile-page.component.scss'],
    providers: [UploadQueueService],
})
export class UserProfilePageComponent implements OnInit, OnDestroy {
    public tabs$ = new BehaviorSubject<{name: string, uri: string}[]>([]);
    private subscriptions: Subscription[] = [];
    public user$ = new BehaviorSubject<User>(null);
    public activeTab: string;

    constructor(
        protected route: ActivatedRoute,
        protected router: Router,
        public settings: Settings,
        public urls: WebPlayerUrls,
        protected users: Users,
        public currentUser: CurrentUser,
        public cd: ChangeDetectorRef,
        public profile: UserProfileService,
        protected modal: Modal,
        protected toast: Toast,
        protected sanitizer: DomSanitizer,
        private reposts: RepostsService,
    ) {}

    ngOnInit() {
        this.route.data.subscribe(data => {
            this.user$.next(data.api.user);
            const tabs = [{uri: 'tracks', name: 'Liked Tracks'}, {uri: 'playlists', name: 'Public Playlists'}];
            if (this.settings.get('player.enable_repost')) {
                tabs.push({uri: 'reposts', name: 'Reposts'});
            }
            tabs.push(...[{uri: 'albums', name: 'Liked Albums'}, {uri: 'artists', name: 'Liked Artists'}, {uri: 'followers', name: 'Followers'}, {uri: 'following', name: 'Following'}]);
            this.tabs$.next(tabs);
            this.setActiveTab(this.router.url);
        });
        const sub = this.router.events
            .pipe(filter(event => event instanceof NavigationEnd))
            .subscribe((event: NavigationEnd) => {
                this.setActiveTab(event.url);
            });
        this.subscriptions.push(sub);
    }

    ngOnDestroy() {
        this.subscriptions.forEach(subscription => {
            subscription.unsubscribe();
        });
        this.subscriptions = [];
    }

    public getProfileBackground() {
        const profile = this.user$.value.profile;
        if (profile.header_colors || profile.header_image) {
            const background = profile.header_image ?
                `url(${profile.header_image})` :
                `linear-gradient(315deg, ${profile.header_colors[0]} 0%, ${profile.header_colors[1]} 100%)`;
            return this.sanitizer.bypassSecurityTrustStyle(background);
        }
    }

    public activeTabIs(name: string) {
        return this.activeTab === name;
    }

    public setActiveTab(url: string) {
        const tabUri = url.split('/').pop();
        const tab = this.tabs$.value.find(t => t.uri === tabUri);
        this.activeTab = tab ? tab.uri : this.tabs$.value[0].uri;
    }

    public openEditProfileModal() {
        this.modal.open(EditUserProfileModalComponent, {user: this.user$.value})
            .beforeClosed()
            .subscribe(updatedUser => {
                if (updatedUser) {
                    this.user$.next({...this.user$.value, ...updatedUser});
                }
            });
    }

    public isSubscribed(): boolean {
        if ( ! this.user$?.value.subscriptions) return false;
        return this.user$.value.subscriptions.find(sub => sub.valid) !== undefined;
    }

    public favicon(url: string) {
        return getFaviconFromUrl(url);
    }

    loadMoreReposts = (page: number): PaginatedBackendResponse<Track|Album> => {
        return this.reposts.getAll({page}).pipe(map((r: any) => {
            r.pagination.data = r.pagination.data.map(repost => repost.repostable);
            return r;
        })) as any;
    }

    loadMoreLikedTracks = (page: number) => {
        return this.profile.likedTracks(this.user$.value.id, {page});
    }

    loadMoreLikedAlbums = (page: number) => {
        return this.profile.likedAlbums(this.user$.value.id, {page});
    }

    loadMoreLikedArtists = (page: number) => {
        return this.profile.likedArtists(this.user$.value.id, {page});
    }

    loadMoreFollowers = (page: number) => {
        return this.profile.followers(this.user$.value.id, {page});
    }

    loadMoreFollowedUsers = (page: number) => {
        return this.profile.followedUsers(this.user$.value.id, {page});
    }

    loadMorePlaylists = (page: number) => {
        return this.profile.playlists(this.user$.value.id, {page});
    }
}
