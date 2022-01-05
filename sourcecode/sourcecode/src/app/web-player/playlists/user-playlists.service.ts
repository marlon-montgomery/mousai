import {Injectable} from '@angular/core';
import {Playlist} from '../../models/Playlist';
import {Playlists} from './playlists.service';
import {CurrentUser} from '@common/auth/current-user';

@Injectable({
    providedIn: 'root'
})
export class UserPlaylists {

    /**
     * All user created and followed playlists.
     */
    private playlists: Playlist[] = [];

    constructor(
        private playlistsApi: Playlists,
        private currentUser: CurrentUser,
    ) {
        this.bindToPlaylistEvents();
    }

    public add(playlists: Playlist|Playlist[]) {
        if ( ! Array.isArray(playlists)) playlists = [playlists];

        playlists = playlists.filter(playlist => {
            return ! this.playlists.find(curr => curr.id === playlist.id);
        });

        this.playlists = playlists.concat(this.playlists);
    }

    public remove(playlist: Playlist) {
        const i = this.playlists.findIndex(predicate => predicate.id === playlist.id);
        this.playlists.splice(i, 1);
    }

    /**
     * Get all user playlists.
     */
    public get() {
        return this.playlists;
    }

    /**
     * Set specified playlists on the service.
     */
    public set(playlists: Playlist[]) {
        if ( ! playlists) playlists = [];
        this.playlists = playlists.slice();
    }

    public follow(playlist: Playlist) {
        this.playlistsApi.follow(playlist.id).subscribe(() => {
            this.add(playlist);
        });
    }

    public unfollow(playlist: Playlist) {
        this.playlistsApi.unfollow(playlist.id).subscribe(() => {
            this.remove(playlist);
        });
    }

    /**
     * Check if current user is following specified playlist.
     */
    public following(id: number): boolean {
        // if user is playlist creator, then he is not following it
        const playlist = this.playlists.find(p => p.id === id);
        return playlist && this.currentUser.get('id') !== playlist.owner_id;
    }

    /**
     * Reset user playlists service.
     */
    public reset() {
        this.playlists = [];
    }

    private bindToPlaylistEvents() {
        this.playlistsApi.deleted$.subscribe(ids => {
            ids.forEach(id => {
                const i = this.playlists.findIndex(playlist => playlist.id === id);
                this.playlists.splice(i, 1);
            });
        });
    }
}
