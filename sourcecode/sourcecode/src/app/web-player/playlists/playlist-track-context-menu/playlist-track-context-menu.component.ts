import {Component, Injector, ViewEncapsulation} from '@angular/core';
import {TrackContextMenuComponent} from '../../tracks/track-context-menu/track-context-menu.component';
import {Player} from '../../player/player.service';
import {UserLibrary} from '../../users/user-library/user-library.service';
import {Playlists} from '../playlists.service';
import {Lyrics} from '../../lyrics/lyrics.service';
import {Tracks} from '../../tracks/tracks.service';

@Component({
    selector: 'playlist-track-context-menu',
    templateUrl: './playlist-track-context-menu.component.html',
    encapsulation: ViewEncapsulation.None,
    host: {'class': 'context-menu'},
})
export class PlaylistTrackContextMenuComponent extends TrackContextMenuComponent {
    constructor(
        protected player: Player,
        protected userLibrary: UserLibrary,
        protected injector: Injector,
        protected playlists: Playlists,
        protected lyrics: Lyrics,
        protected tracks: Tracks,
    ) {
        super(player, userLibrary, injector, lyrics, tracks);
    }

    public removeFromPlaylist() {
        this.playlists.removeTracks(this.data.playlistId, this.getTracks()).subscribe();
        this.contextMenu.close();
    }
}
