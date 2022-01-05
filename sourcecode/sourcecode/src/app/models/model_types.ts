import {ARTIST_MODEL} from './Artist';
import {ALBUM_MODEL} from './Album';
import {TRACK_MODEL} from './Track';
import {PLAYLIST_MODEL} from './Playlist';
import {USER_MODEL} from '@common/core/types/models/User';
import {GENRE_MODEL} from './Genre';
import {CHANNEL_MODEL} from '../admin/channels/channel';

export const CHANNEL_MODEL_TYPES = {
    artist: ARTIST_MODEL,
    album: ALBUM_MODEL,
    track: TRACK_MODEL,
    playlist: PLAYLIST_MODEL,
    user: USER_MODEL,
    genre: GENRE_MODEL,
    channel: CHANNEL_MODEL,
};

export const MAIN_SEARCH_MODELS = [
    ARTIST_MODEL,
    ALBUM_MODEL,
    TRACK_MODEL,
    USER_MODEL,
    PLAYLIST_MODEL,
];
