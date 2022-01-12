import {Component, OnInit, ViewEncapsulation} from '@angular/core';
import {Settings} from '@common/core/config/settings.service';
import {Albums} from '../../../web-player/albums/albums.service';
import {Album, ALBUM_MODEL} from '../../../models/Album';
import {CurrentUser} from '@common/auth/current-user';
import {WebPlayerUrls} from '../../../web-player/web-player-urls.service';
import {WebPlayerImagesService} from '../../../web-player/web-player-images.service';
import {DatatableService} from '@common/datatable/datatable.service';
import {Observable} from 'rxjs';
import {ImportMediaModalComponent} from '../../import-media-modal/import-media-modal.component';
import {Modal} from '@common/core/ui/dialogs/modal.service';
import {ALBUM_INDEX_FILTERS} from './album-index-filters';

@Component({
    selector: 'album-index',
    templateUrl: './album-index.component.html',
    styleUrls: ['./album-index.component.scss'],
    encapsulation: ViewEncapsulation.None,
    providers: [DatatableService],
})
export class AlbumIndexComponent implements OnInit {
    public albums$ = this.datatable.data$ as Observable<Album[]>;
    filters = ALBUM_INDEX_FILTERS;
    constructor(
        public datatable: DatatableService<Album>,
        public settings: Settings,
        private albums: Albums,
        public currentUser: CurrentUser,
        public urls: WebPlayerUrls,
        public images: WebPlayerImagesService,
        private modal: Modal,
    ) {}

    ngOnInit() {
        this.datatable.sort$.next({orderBy: 'plays'});
        this.datatable.init({
            uri: 'albums',
            staticParams: {withCount: 'tracks'}
        });
    }

    public maybeDeleteSelectedAlbums() {
        this.datatable.confirmResourceDeletion('albums').subscribe(() => {
            this.albums.delete(this.datatable.selectedRows$.value).subscribe(() => {
                this.datatable.reset();
            });
        });
    }

    public openImportMedialModal() {
        this.modal.open(ImportMediaModalComponent, {modelType: ALBUM_MODEL})
            .afterClosed()
            .subscribe(response => {
                if (response) {
                    this.datatable.reset();
                }
            });
    }
}
