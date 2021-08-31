import {
    Component,
    Input,
    OnChanges,
    OnDestroy,
    OnInit,
    SimpleChanges
} from '@angular/core';
import {Album} from '../../../../models/Album';
import {WebPlayerUrls} from '../../../web-player-urls.service';
import {AlbumContextMenuComponent} from '../../../albums/album-context-menu/album-context-menu.component';
import {ContextMenu} from '@common/core/ui/context-menu/context-menu.service';
import {Artist} from '../../../../models/Artist';
import {DatatableService} from '@common/datatable/datatable.service';
import {Track} from '../../../../models/Track';
import {queueId} from '../../../player/queue-id';

@Component({
    selector: 'artist-page-album-list-item',
    templateUrl: './artist-page-album-list-item.component.html',
    styleUrls: ['./artist-page-album-list-item.component.scss'],
    providers: [DatatableService],
})
export class ArtistPageAlbumListItemComponent implements OnInit, OnChanges, OnDestroy {
    @Input() album: Album;
    @Input() artist: Artist;
    public albumQueueId: string;

    constructor(
        public urls: WebPlayerUrls,
        public datatable: DatatableService<Track>,
        private contextMenu: ContextMenu,
    ) {}

    ngOnInit() {
        this.datatable.init();
    }

    ngOnDestroy() {
        this.datatable.destroy();
    }

    ngOnChanges(changes: SimpleChanges) {
        if (changes.album.currentValue !== changes.album.previousValue) {
            this.albumQueueId = queueId(this.album, 'allTracks');
            this.datatable.data = this.album.tracks;
        }
    }

    public showAlbumContextMenu(album: Album, e: MouseEvent) {
        e.stopPropagation();
        this.contextMenu.open(
            AlbumContextMenuComponent,
            e.target,
            {overlayY: 'center', data: {item: album, type: 'album'}}
        );
    }
}
