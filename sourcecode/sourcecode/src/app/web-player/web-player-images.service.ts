import {Injectable} from '@angular/core';
import {Playlist} from '../models/Playlist';
import {Album} from '../models/Album';
import {Track} from '../models/Track';
import {DefaultImagePaths} from './default-image-paths.enum';

@Injectable({
    providedIn: 'root',
})
export class WebPlayerImagesService {
    public getDefault(type: 'artist'|'album'|'artistBig'): string {
        if (type === 'artist') {
            return DefaultImagePaths.artistSmall;
        } else if (type === 'artistBig') {
            return DefaultImagePaths.artistBig;
        } else {
            return DefaultImagePaths.album;
        }
    }

    public getPlaylistImage(playlist: Playlist): string {
        if (playlist.image) return playlist.image;
        if (playlist.tracks && playlist.tracks[0] &&  playlist.tracks[0].album) return playlist.tracks[0].album.image;
        return this.getDefault('album');
    }

    public getAlbumImage(album: Album): string {
        if (album && album.image) return album.image;
        return this.getDefault('album');
    }

    public getTrackImage(track: Track) {
        if (track.image) {
            return track.image;
        } else if (track.album && track.album.image) {
            return track.album.image;
        } else {
            return DefaultImagePaths.album;
        }
    }
}
