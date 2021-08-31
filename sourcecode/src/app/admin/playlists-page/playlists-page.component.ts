import {Component, OnInit, ViewEncapsulation} from '@angular/core';
import {CurrentUser} from '@common/auth/current-user';
import {WebPlayerImagesService} from '../../web-player/web-player-images.service';
import {Playlist, PLAYLIST_MODEL} from '../../models/Playlist';
import {Playlists} from '../../web-player/playlists/playlists.service';
import {CrupdatePlaylistModalComponent} from '../../web-player/playlists/crupdate-playlist-modal/crupdate-playlist-modal.component';
import {DatatableService} from '@common/datatable/datatable.service';
import {Observable} from 'rxjs';
import {WebPlayerUrls} from '../../web-player/web-player-urls.service';
import {ImportMediaModalComponent} from '../import-media-modal/import-media-modal.component';
import {ARTIST_MODEL} from '../../models/Artist';
import {Modal} from '@common/core/ui/dialogs/modal.service';
import {Settings} from '@common/core/config/settings.service';
import {PLAYLIST_INDEX_FILTERS} from './playlist-index-filters';

@Component({
    selector: 'playlists-page',
    templateUrl: './playlists-page.component.html',
    encapsulation: ViewEncapsulation.None,
    providers: [DatatableService],
})
export class PlaylistsPageComponent implements OnInit {
    playlists$ = this.datatable.data$ as Observable<Playlist[]>;
    filters = PLAYLIST_INDEX_FILTERS;
    constructor(
        public datatable: DatatableService<Playlist>,
        private playlists: Playlists,
        public currentUser: CurrentUser,
        public settings: Settings,
        public wpImages: WebPlayerImagesService,
        public urls: WebPlayerUrls,
        private modal: Modal,
    ) {}

    ngOnInit() {
        this.datatable.init({
            uri: 'playlists',
            staticParams: {with: ['owner']},
        });
    }

    public showCrupdatePlaylistModal(playlist?: Playlist) {
        this.datatable.openCrupdateResourceModal(CrupdatePlaylistModalComponent, {playlist})
            .subscribe(() => {
                this.datatable.reset();
            });
    }

    public confirmPlaylistsDeletion() {
        this.datatable.confirmResourceDeletion('playlists').subscribe(confirmed => {
            const ids = this.datatable.selectedRows$.value;
            this.playlists.delete(ids).subscribe(() => {
                this.datatable.reset();
            });
        });
    }

    public openImportMedialModal() {
        this.modal.open(ImportMediaModalComponent, {modelType: PLAYLIST_MODEL})
            .afterClosed()
            .subscribe(response => {
                if (response) {
                    this.datatable.reset();
                }
            });
    }
}
