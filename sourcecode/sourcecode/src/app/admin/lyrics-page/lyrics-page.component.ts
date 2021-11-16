import {Component, OnInit, ViewEncapsulation} from '@angular/core';
import {WebPlayerImagesService} from '../../web-player/web-player-images.service';
import {Lyric} from '../../models/Lyric';
import {Lyrics} from '../../web-player/lyrics/lyrics.service';
import {Modal} from '@common/core/ui/dialogs/modal.service';
import {CurrentUser} from '@common/auth/current-user';
import {Settings} from '@common/core/config/settings.service';
import {CrupdateLyricModalComponent} from './crupdate-lyric-modal/crupdate-lyric-modal.component';
import {DatatableService} from '../../../common/datatable/datatable.service';
import {Observable} from 'rxjs';

@Component({
    selector: 'lyrics-page',
    templateUrl: './lyrics-page.component.html',
    styleUrls: ['./lyrics-page.component.scss'],
    encapsulation: ViewEncapsulation.None,
    providers: [DatatableService],
})
export class LyricsPageComponent implements OnInit {
    public lyrics$ = this.datatable.data$ as Observable<Lyric[]>;
    constructor(
        public datatable: DatatableService<Lyric>,
        private lyrics: Lyrics,
        private modal: Modal,
        public currentUser: CurrentUser,
        private settings: Settings,
        private images: WebPlayerImagesService,
    ) {}

    ngOnInit() {
        this.datatable.init({
            uri: 'lyrics',
            staticParams: {with: 'track.album.artists'},
        });
    }

    public openCrupdateLyricModal(lyric?: Lyric) {
        this.datatable.openCrupdateResourceModal(CrupdateLyricModalComponent, {lyric})
            .subscribe(() => {
                this.datatable.reset();
            });
    }

    public confirmLyricsDeletion() {
        this.datatable.confirmResourceDeletion('lyrics').subscribe(() => {
            this.lyrics.delete(this.datatable.selectedRows$.value).subscribe(() => {
                this.datatable.reset();
            });
        });
    }

    public getLyricImage(lyric: Lyric): string {
        return lyric?.track?.album?.image || this.images.getDefault('album');
    }
}
