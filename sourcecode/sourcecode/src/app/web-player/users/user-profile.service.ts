import {ChangeDetectorRef, Injectable} from '@angular/core';
import {BackendResponse} from '@common/core/types/backend-response';
import {User} from '@common/core/types/models/User';
import {PaginatedBackendResponse} from '@common/core/types/pagination/paginated-backend-response';
import {CurrentUser} from '@common/auth/current-user';
import {HttpCacheClient} from '@common/core/http/http-cache-client';
import {Track} from '../../models/Track';
import {Album} from '../../models/Album';
import {Playlist} from '../../models/Playlist';
import {Artist} from '../../models/Artist';

const BASE_URI = 'users';

export interface GetProfileResponse {
    user: User;
}

@Injectable({
    providedIn: 'root'
})
export class UserProfileService {
    constructor(private http: HttpCacheClient, private currentUser: CurrentUser) {}

    public get(id: number): BackendResponse<GetProfileResponse> {
        return this.http.get(`${BASE_URI}/${id}`);
    }

    public update(params: object): BackendResponse<{user: User}> {
        return this.http.put(`${BASE_URI}/profile/update`, params);
    }

    public likedTracks(userId: number, params: {page: number}): PaginatedBackendResponse<Track> {
        return this.http.getWithCache(`${BASE_URI}/${userId}/liked-tracks`, params);
    }

    public likedAlbums(userId: number, params: {page: number}): PaginatedBackendResponse<Album> {
        return this.http.getWithCache(`${BASE_URI}/${userId}/liked-albums`, params);
    }

    public likedArtists(userId: number, params: {page: number}): PaginatedBackendResponse<Artist> {
        return this.http.getWithCache(`${BASE_URI}/${userId}/liked-artists`, params);
    }

    public followers(userId: number, params: {page: number}): PaginatedBackendResponse<User> {
        return this.http.getWithCache(`${BASE_URI}/${userId}/followers`, params);
    }

    public followedUsers(userId: number, params: {page: number}): PaginatedBackendResponse<User> {
        return this.http.getWithCache(`${BASE_URI}/${userId}/followed-users`, params);
    }

    public playlists(userId: number, params: {page: number}): PaginatedBackendResponse<Playlist> {
        return this.http.getWithCache(`${BASE_URI}/${userId}/playlists`, params);
    }

    public follow(user: User, cd: ChangeDetectorRef) {
        this.http.post(`users/${user.id}/follow`).subscribe(() => {
            this.currentUser.getModel().followed_users.push(user);
            cd.markForCheck();
        });
    }

    public unfollow(user: User, cd: ChangeDetectorRef) {
        this.http.post(`users/${user.id}/unfollow`).subscribe(() => {
            const followedUsers = this.currentUser.getModel().followed_users;
            const i = followedUsers.findIndex(curr => curr.id === user.id);
            followedUsers.splice(i, 1);
            cd.markForCheck();
        });
    }

    public currentUserIsFollowing(user: User): boolean {
        if ( ! this.currentUser.getModel().followed_users) return false;
        return !!this.currentUser.getModel().followed_users.find(curr => curr.id === user.id);
    }
}
