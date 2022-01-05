import {finalize} from 'rxjs/operators';
import {Component, Inject, Optional} from '@angular/core';
import {Playlist} from '../../../models/Playlist';
import {Playlists} from '../playlists.service';
import {Settings} from '@common/core/config/settings.service';
import {Observable} from 'rxjs';
import {MAT_DIALOG_DATA, MatDialogRef} from '@angular/material/dialog';
import {WebPlayerImagesService} from '../../web-player-images.service';
import {ImageUploadValidator} from '../../image-upload-validator';
import {UploadQueueService} from '@common/uploads/upload-queue/upload-queue.service';
import {BackendErrorResponse} from '@common/core/types/backend-error-response';
import {UploadApiConfig} from '@common/uploads/types/upload-api-config';
import {FormControl, FormGroup} from '@angular/forms';
import {DefaultImagePaths} from '../../default-image-paths.enum';
import {BackendResponse} from '@common/core/types/backend-response';

export interface CrupdatePlaylistModalData {
    playlist?: Playlist;
}

@Component({
    selector: 'crupdate-playlist-modal',
    templateUrl: './crupdate-playlist-modal.component.html',
    styleUrls: ['./crupdate-playlist-modal.component.scss'],
    providers: [UploadQueueService],
})
export class CrupdatePlaylistModalComponent {
    public loading = false;
    public errors: {description?: string, name?: string} = {};
    public form = new FormGroup({
        name: new FormControl(''),
        description: new FormControl(''),
        image: new FormControl(''),
        public: new FormControl(false),
        collaborative: new FormControl(false),
    });
    public defaultImage = DefaultImagePaths.track;
    public uploadImgConfig = {
        uri: 'uploads/images',
        httpParams: {diskPrefix: 'playlist_media', disk: 'public'},
        validator: this.imageValidator
    } as UploadApiConfig;

    constructor(
        private playlists: Playlists,
        private settings: Settings,
        private uploadQueue: UploadQueueService,
        private dialogRef: MatDialogRef<CrupdatePlaylistModalComponent>,
        public images: WebPlayerImagesService,
        private imageValidator: ImageUploadValidator,
        @Optional() @Inject(MAT_DIALOG_DATA) public data?: CrupdatePlaylistModalData,
    ) {
        this.hydrate();
    }

    public confirm() {
        this.loading = true;

        this.crupdatePlaylist().pipe(finalize(() => {
            this.loading = false;
        })).subscribe(response => {
            this.close(response.playlist);
        }, (errResponse: BackendErrorResponse) => this.errors = errResponse.errors);
    }

    public close(playlist?: Playlist) {
        this.dialogRef.close(playlist);
    }

    private crupdatePlaylist() {
        const payload = this.form.value;

        if (this.data.playlist) {
            return this.playlists.update(this.data.playlist.id, payload);
        } else {
            return this.playlists.create(payload);
        }
    }

    private hydrate() {
        if (this.data.playlist) {
            this.form.patchValue(this.data.playlist);
        }
    }
}
