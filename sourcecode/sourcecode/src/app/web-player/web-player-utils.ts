import {Track} from '../models/Track';
import {Album} from '../models/Album';

export class WpUtils {

    public static assignAlbumToTracks(tracks: Track[] = [], album: Album) {
        album = album ? {id: album.id, name: album.name, image: album.image} : {} as any;

        if ( ! tracks) {
            tracks = [];
        }

        tracks.forEach(track => {
            track.album = track.album || album;
            return track;
        });
        return [...tracks];
    }
}

