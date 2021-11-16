import {Component, OnInit, ViewEncapsulation} from '@angular/core';
import {Artist, ARTIST_MODEL} from '../../models/Artist';
import {Artists} from '../../web-player/artists/artists.service';
import {CurrentUser} from '@common/auth/current-user';
import {WebPlayerUrls} from '../../web-player/web-player-urls.service';
import {DatatableService} from '@common/datatable/datatable.service';
import {Observable} from 'rxjs';
import {Modal} from '@common/core/ui/dialogs/modal.service';
import {ImportMediaModalComponent} from '../import-media-modal/import-media-modal.component';
import {Settings} from '@common/core/config/settings.service';

@Component({
    selector: 'artist-index',
    templateUrl: './artist-index.component.html',
    encapsulation: ViewEncapsulation.None,
    providers: [DatatableService],
})
export class ArtistIndexComponent implements OnInit {
    public artists$ = this.datatable.data$ as Observable<Artist[]>;
    constructor(
        public datatable: DatatableService<Artist>,
        private artists: Artists,
        public currentUser: CurrentUser,
        public urls: WebPlayerUrls,
        public modal: Modal,
        public settings: Settings,
    ) {}

    ngOnInit() {
        this.datatable.sort$.next({orderBy: 'plays'});
        this.datatable.init({
            uri: 'artists',
        });
    }

    public maybeDeleteSelectedArtists() {
        this.datatable.confirmResourceDeletion('artists').subscribe(() => {
            this.artists.delete(this.datatable.selectedRows$.value).subscribe(() => {
                this.datatable.reset();
            });
        });
    }

    public openImportMedialModal() {
        this.modal.open(ImportMediaModalComponent, {modelType: ARTIST_MODEL})
            .afterClosed()
            .subscribe(response => {
                if (response) {
                    this.datatable.reset();
                }
            });
    }
}
