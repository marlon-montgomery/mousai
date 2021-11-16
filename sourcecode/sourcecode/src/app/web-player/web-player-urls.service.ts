import {Injectable} from '@angular/core';
import {Album} from '../models/Album';
import {Genre} from '../models/Genre';
import {Track} from '../models/Track';
import {Playlist} from '../models/Playlist';
import {Settings} from '@common/core/config/settings.service';
import {User} from '@common/core/types/models/User';
import {slugifyString} from '@common/core/utils/slugify-string';
import {UserArtist} from '../models/App-User';
import {Channel} from '../admin/channels/channel';
import {Artist} from '../models/Artist';

@Injectable({
    providedIn: 'root'
})
export class WebPlayerUrls {
    constructor(private settings: Settings) {}

    public album(album: Album, artist?: Artist) {
        if ( ! artist && album.artists) artist = album.artists[0];
        const artistName = artist ? artist.name : 'Various Artists';
        return ['/album', album.id, this.encodeItemName(artistName), this.encodeItemName(album.name)];
    }

    public artist(artist: Artist|UserArtist|number) {
        const artistId = typeof artist === 'number' ? artist : artist.id;
        const link = ['/artist', artistId];
        if (typeof artist !== 'number') {
            link.push(this.encodeItemName(artist.name));
        }
        return link;
    }

    public artistRadio(artist: Artist) {
        return ['radio/artist', artist.id, this.encodeItemName(artist.name)];
    }

    public trackRadio(track: Track) {
        return ['radio/track', track.id, this.encodeItemName(track.name)];
    }

    public genre(genre: Genre) {
        return ['/channel/genre', this.encodeItemName(genre.name)];
    }

    public track(track: Track) {
        return ['/track', track.id, this.encodeItemName(track.name)];
    }

    public trackDownload(track: Track) {
        return this.settings.getBaseUrl(true) + 'secure/tracks/' + track.id + '/download';
    }

    public playlist(playlist: Playlist) {
        return ['/playlist', playlist.id, this.encodeItemName(playlist.name)];
    }

    public user(user: Partial<User>, append?: string) {
        if ( ! user) return ['/'];
        const link = ['/user', user.id, this.encodeItemName(user.display_name)];
        if (append) link.push(append);
        return link;
    }

    public search(query: string, tab?: string) {
        const link = ['/search', query || ''];
        if (tab) link.push(tab);
        return link;
    }

    public channel(channel: Channel) {
        return ['/channel', channel.slug];
    }

    public editArtist(artistId: number, admin?: boolean): any[] {
        const prefix = admin ? '/admin' : '';
        return [`${prefix}/backstage/artists`, artistId, 'edit'];
    }

    public editAlbum(album: Album, admin?: boolean): any[] {
        const prefix = admin ? '/admin' : '';
        return [`${prefix}/backstage/albums`, album.id, 'edit'];
    }

    public createAlbum(admin?: boolean): any[] {
        const prefix = admin ? '/admin' : '';
        return [`${prefix}/backstage/albums`, 'new'];
    }

    public editTrack(track: Track, admin?: boolean): any[] {
        const prefix = admin ? '/admin' : '';
        return [`${prefix}/backstage/tracks`, track.id, 'edit'];
    }

    public encodeItemName(name: string): string {
        if ( ! name) return '';
        return slugifyString(name);
    }

    public routerLinkToUrl(commands: any[]): string {
        const uri = commands.map(command => {
            if (typeof command === 'string') {
                command = this.encodeItemName(command);
            }
            return command;
        }).join('/').replace(/^\/|\/$/g, '');
        return (this.settings.getBaseUrl() + uri);
    }
}
