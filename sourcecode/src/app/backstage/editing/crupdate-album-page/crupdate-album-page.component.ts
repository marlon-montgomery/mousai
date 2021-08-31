import {ChangeDetectionStrategy, Component, OnInit, ViewChild} from '@angular/core';
import {ActivatedRoute, Router} from '@angular/router';
import {Album} from '../../../models/Album';
import {UploadQueueService} from '@common/uploads/upload-queue/upload-queue.service';
import {ComponentCanDeactivate} from '@common/guards/pending-changes/component-can-deactivate';
import {AlbumFormComponent} from '../../../uploading/album-form/album-form.component';
import {Settings} from '@common/core/config/settings.service';
import {WebPlayerUrls} from '../../../web-player/web-player-urls.service';
import {Artist} from '../../../models/Artist';

@Component({
    selector: 'crupdate-album-page',
    templateUrl: './crupdate-album-page.component.html',
    styleUrls: ['./crupdate-album-page.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush,
    providers: [UploadQueueService],
})
export class CrupdateAlbumPageComponent implements OnInit, ComponentCanDeactivate {
    @ViewChild(AlbumFormComponent, {static: true}) albumForm: AlbumFormComponent;
    public album: Album;
    public artist: Artist;

    constructor(
        private route: ActivatedRoute,
        private router: Router,
        public settings: Settings,
        public urls: WebPlayerUrls,
    ) {}

    ngOnInit() {
        this.route.data.subscribe(data => {
            if (data.api) {
                this.album = data.api.album;
            }
        });
        this.route.queryParams.subscribe(params => {
            if (params.artist) {
                this.artist = JSON.parse(atob(params.artist));
            }
        });
    }

    public toAlbumsPage() {
        if (this.router.url.includes('admin')) {
            if (this.artist) {
                this.router.navigate(this.urls.editArtist(this.artist.id, true));
            } else {
                this.router.navigate(['/admin/albums']);
            }
        } else {
            this.router.navigate(['/']);
        }
    }

    public canDeactivate() {
        if (this.albumForm.form.dirty) {
            return false;
        } else if (this.albumForm.trackForms.some(tf => tf.form.dirty)) {
            return false;
        }
        return true;
    }
}
