import {ChangeDetectionStrategy, Component, Inject, OnInit} from '@angular/core';
import {MAT_DIALOG_DATA, MatDialogRef} from '@angular/material/dialog';
import {FormControl, FormGroup} from '@angular/forms';
import {BehaviorSubject} from 'rxjs';
import {AppHttpClient} from '@common/core/http/app-http-client.service';
import {Toast} from '@common/core/ui/toast.service';
import {finalize} from 'rxjs/operators';
import {ARTIST_MODEL} from '../../models/Artist';
import {ALBUM_MODEL} from '../../models/Album';
import {TRACK_MODEL} from '../../models/Track';
import {PLAYLIST_MODEL} from '../../models/Playlist';
import {BackendErrorResponse} from '@common/core/types/backend-error-response';

interface ImportMediaModalData {
    modelType: string;
}

@Component({
    selector: 'import-media-modal',
    templateUrl: './import-media-modal.component.html',
    styleUrls: ['./import-media-modal.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush
})
export class ImportMediaModalComponent implements OnInit {
    public loading$ = new BehaviorSubject<boolean>(false);
    public form = new FormGroup({
        spotifyId: new FormControl(),
        importAlbums: new FormControl(true),
        importLyrics: new FormControl(true),
        importSimilarArtists: new FormControl(false),
    });
    public resourceName: string;
    public importingArtist: boolean;
    public importingAlbum: boolean;
    public importingTrack: boolean;
    public importingPlaylist: boolean;

    constructor(
        private dialogRef: MatDialogRef<ImportMediaModalComponent>,
        private http: AppHttpClient,
        private toast: Toast,
        @Inject(MAT_DIALOG_DATA) public data: ImportMediaModalData,
    ) {}

    ngOnInit() {
        this.resourceName = this.data.modelType.split('\\').pop();
        this.importingArtist = this.data.modelType === ARTIST_MODEL;
        this.importingAlbum = this.data.modelType === ALBUM_MODEL;
        this.importingTrack = this.data.modelType === TRACK_MODEL;
        this.importingPlaylist = this.data.modelType === PLAYLIST_MODEL;
    }

    public import() {
        this.loading$.next(true);
        this.http.post('import-media/single-item', {...this.data, ...this.form.value})
            .pipe(finalize(() => this.loading$.next(false)))
            .subscribe(response => {
                this.toast.open(`${this.resourceName} imported`);
                this.close(response);
            }, (err: BackendErrorResponse) => {
                this.toast.open(err.message || 'Could not import media.');
            });
    }

    public close(response?: any) {
        this.dialogRef.close(response);
    }
}
