import {Track} from '../../models/Track';

export function trackIsLocallyUploaded(track: Track): boolean {
    return track.url && (track.url.startsWith('storage') || track.url.includes('storage/track_media'));
}
