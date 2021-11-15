import {Album} from '../../models/Album';
import {Track} from '../../models/Track';
import {Playlist} from '../../models/Playlist';
import {User} from '@common/core/types/models/User';
import {Channel} from '../../admin/channels/channel';
import {Genre} from '../../models/Genre';
import {Tag} from '@common/core/types/models/Tag';
import {Artist} from '../../models/Artist';

export interface SearchResults {
    artists?: Artist[];
    albums?: Album[];
    tracks?: Track[];
    playlists?: Playlist[];
    users?: User[];
    channels?: Channel[];
    genres?: Genre[];
    tags?: Tag[];
}

export interface SearchResponse {
    query: string;
    results: SearchResults;
}
