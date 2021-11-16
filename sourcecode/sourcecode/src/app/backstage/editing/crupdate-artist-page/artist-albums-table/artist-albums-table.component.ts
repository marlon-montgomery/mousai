import {
    Component,
    Input,
    OnChanges,
    OnInit,
    SimpleChanges,
    ViewEncapsulation
} from '@angular/core';
import {CurrentUser} from '@common/auth/current-user';
import {WebPlayerImagesService} from '../../../../web-player/web-player-images.service';
import {Artist} from '../../../../models/Artist';
import {Album} from '../../../../models/Album';
import {Albums} from '../../../../web-player/albums/albums.service';
import {Modal} from '@common/core/ui/dialogs/modal.service';
import {ConfirmModalComponent} from '@common/core/ui/confirm-modal/confirm-modal.component';
import {DatatableService} from '@common/datatable/datatable.service';
import {Observable} from 'rxjs';
import {WebPlayerUrls} from '../../../../web-player/web-player-urls.service';

@Component({
    selector: 'artist-albums-table',
    templateUrl: './artist-albums-table.component.html',
    styleUrls: ['./artist-albums-table.component.scss'],
    encapsulation: ViewEncapsulation.None,
    providers: [DatatableService],
})
export class ArtistAlbumsTableComponent implements OnInit, OnChanges {
    @Input() artist: Artist;
    @Input() albums: Album[] = [];
    public albums$ = this.datatable.data$ as Observable<Album[]>;
    public encodedArtist: string;
    constructor(
        private modal: Modal,
        private albumsApi: Albums,
        public currentUser: CurrentUser,
        public images: WebPlayerImagesService,
        public datatable: DatatableService<Album>,
        public urls: WebPlayerUrls,
    ) {}

    public ngOnChanges(changes: SimpleChanges) {
        console.log('changes');
        if (this.artist) {
            this.encodedArtist = btoa(JSON.stringify({
                id: this.artist.id,
                name: this.artist.name,
                image_small: this.artist.image_small
            }));
        }
    }

    ngOnInit() {
        console.log();
        this.datatable.init({
            initialData: this.albums ? this.albums : []
        });
    }

    public maybeDeleteAlbum(album: Album) {
        this.modal.show(ConfirmModalComponent, {
            title: 'Delete Album',
            body: 'Are you sure you want to delete this album?',
            ok: 'Delete'
        }).beforeClosed().subscribe(async (confirmed) => {
            if ( ! confirmed) return;
            this.albumsApi.delete([album.id]).subscribe(() => {
                this.datatable.data = this.datatable.data.filter(a => a.id !== album.id);
            });
        });
    }
}
