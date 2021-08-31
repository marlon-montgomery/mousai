import {ArtistContextMenuComponent} from './artists/artist-context-menu/artist-context-menu.component';
import {AlbumContextMenuComponent} from './albums/album-context-menu/album-context-menu.component';
import {PlaylistTrackContextMenuComponent} from './playlists/playlist-track-context-menu/playlist-track-context-menu.component';
import {TrackContextMenuComponent} from './tracks/track-context-menu/track-context-menu.component';
import {PlaylistContextMenuComponent} from './playlists/playlist-context-menu/playlist-context-menu.component';

export const WEB_PLAYER_CONTEXT_MENUS = {
    artist: ArtistContextMenuComponent,
    album: AlbumContextMenuComponent,
    track: TrackContextMenuComponent,
    playlist: PlaylistContextMenuComponent,
    playlistTrack: PlaylistTrackContextMenuComponent,
};