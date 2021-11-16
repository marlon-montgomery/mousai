import {
    ChangeDetectionStrategy,
    ChangeDetectorRef,
    Component,
    EventEmitter,
    Input,
    OnInit,
    Output,
    QueryList,
    ViewChildren
} from '@angular/core';
import {FormBuilder} from '@angular/forms';
import {UploadQueueService} from '@common/uploads/upload-queue/upload-queue.service';
import {UploadQueueItem} from '@common/uploads/upload-queue/upload-queue-item';
import {CdkDragDrop, moveItemInArray} from '@angular/cdk/drag-drop';
import {Albums} from '../../web-player/albums/albums.service';
import {TrackFormComponent} from '../track-form/track-form.component';
import {Album} from '../../models/Album';
import {BehaviorSubject} from 'rxjs';
import {finalize, map} from 'rxjs/operators';
import {Modal} from '@common/core/ui/dialogs/modal.service';
import {ConfirmModalComponent} from '@common/core/ui/confirm-modal/confirm-modal.component';
import {Toast} from '@common/core/ui/toast.service';
import {AudioUploadValidator} from '../../web-player/audio-upload-validator';
import {Track} from '../../models/Track';
import {UploadedFile} from '@common/uploads/uploaded-file';
import {UploadInputConfig, UploadInputTypes} from '@common/uploads/upload-input-config';
import {Settings} from '@common/core/config/settings.service';
import {scrollInvalidInputIntoView} from '@common/core/utils/scroll-invalid-input-into-view';
import {UploadApiConfig} from '@common/uploads/types/upload-api-config';
import {Search} from '../../web-player/search/search.service';
import {GENRE_MODEL} from '../../models/Genre';
import {BackendErrorResponse} from '@common/core/types/backend-error-response';
import {AppCurrentUser} from '../../app-current-user';
import {TAG_MODEL} from '@common/core/types/models/Tag';
import {Artist} from '../../models/Artist';

@Component({
    selector: 'album-form',
    templateUrl: './album-form.component.html',
    styleUrls: ['./album-form.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class AlbumFormComponent implements OnInit {
    public uploadButtonConfig: UploadInputConfig = {multiple: true, types: [UploadInputTypes.audio, UploadInputTypes.video]};
    // album that is being edited
    @Input() album: Album;
    @Input() artist: Artist;
    @Input() confirmCancel = true;
    @Output() canceled = new EventEmitter();
    @Output() saved = new EventEmitter<Album>();
    @ViewChildren(TrackFormComponent) trackForms: QueryList<TrackFormComponent>;

    public errors: {[K in keyof Partial<Album>]: string} = {};
    public loading$ = new BehaviorSubject(false);
    public allTracks$ = new BehaviorSubject<(UploadQueueItem|Track)[]>([]);

    public form = this.fb.group({
        name: [''],
        image: [''],
        artists: [[]],
        release_date: [new Date().toISOString().slice(0, 10)],
        description: [''],
        tags: [[]],
        genres: [[]],
    });

    constructor(
        private fb: FormBuilder,
        public uploadQueue: UploadQueueService,
        private albums: Albums,
        private cd: ChangeDetectorRef,
        public currentUser: AppCurrentUser,
        private modal: Modal,
        private toast: Toast,
        private audioValidator: AudioUploadValidator,
        public settings: Settings,
        private search: Search,
    ) {}

    ngOnInit() {
        this.allTracks$ = new BehaviorSubject([
            ...(this.album ? this.album.tracks : []),
            ...this.onlyValidUploads(this.uploadQueue.uploads$.value),
        ]);
        this.uploadQueue.uploadsAdded$.subscribe(uploads => {
            this.allTracks$.next([
                ...this.allTracks$.value,
                ...this.onlyValidUploads(uploads),
            ]);
        });

        // if we are editing an album, hydrate the form
        if (this.album) {
            const value = {
                ...this.album,
                tags: this.album.tags.map(t => t.name),
                genres: this.album.genres.map(g => g.display_name || g.name),
            };
            this.form.patchValue(value);
        } else if (this.artist) {
            this.form.get('artists').setValue([this.artist]);
        // set album artist as primary artist of current user
        } else if ( ! this.currentUser.canAttachMusicToAnyArtist()) {
            this.form.get('artists').setValue([
                this.currentUser.get('artists')[0] || this.currentUser.artistPlaceholder(),
            ]);
        }
    }

    private onlyValidUploads(uploads: UploadQueueItem[]) {
        return uploads.filter(upload => !upload.error$.value);
    }

    public reorderTracks(e: CdkDragDrop<never>) {
        const sortedTracks = this.allTracks$.value;
        moveItemInArray(sortedTracks, e.previousIndex, e.currentIndex);
        this.allTracks$.next(sortedTracks);
    }

    public submit() {
        if (this.trackForms.some(f => f.isUploading())) {
            this.toast.open('Some tracks are still uploading or failed to upload.');
            return;
        }

        this.loading$.next(true);

        const payload = {
            ...this.form.value,
            tracks: this.trackForms.map(f => f.getPayload()),
        };
        payload.artists = payload.artists.map(a => typeof a !== 'number' ? a.id : a);

        const request = this.album ?
            this.albums.update(this.album.id, payload) :
            this.albums.create(payload);

        request
            .pipe(finalize(() => this.loading$.next(false)))
            .subscribe(response => {
                this.form.markAsPristine();
                this.trackForms.forEach(tf => tf.form.markAsPristine());
                this.toast.open('Album saved.');
                this.uploadQueue.reset();
                this.saved.emit(response.album);
            }, (errResponse: BackendErrorResponse) => {
                this.errors = errResponse.errors;
                scrollInvalidInputIntoView(this.errors, 'track-form');
                this.cd.markForCheck();
            });
    }

    public maybeCancel() {
        if ( ! this.confirmCancel) {
            this.canceled.emit();
            return;
        }
        this.modal.show(ConfirmModalComponent, {
            title: 'Delete Album',
            body:  'Are you sure you want to cancel all uploads and delete this album?',
            ok:    'Delete'
        }).beforeClosed().subscribe(confirmed => {
            if (confirmed) {
                this.form.reset();
                this.uploadQueue.reset();
                this.canceled.emit();
            }
        });
    }

    public uploadFiles(uploadedFiles: UploadedFile[]) {
        const params = {
            uri: 'uploads',
            httpParams: {diskPrefix: 'track_media', disk: 'public'},
            validator: this.audioValidator
        } as UploadApiConfig;
        this.uploadQueue.start(uploadedFiles, params).subscribe(response => {
            this.trackForms.find(tf => tf.uploadQueueItem?.id === response.queueItemId).form.markAsDirty();
        }, () => this.toast.open('Could not upload tracks.'));
    }

    public trackRemoved(track: UploadQueueItem | Track) {
        const newTracks = this.allTracks$.value.filter(t => t.id !== track.id);
        this.allTracks$.next(newTracks);
    }

    public trackByFn = (i: number, upload: UploadQueueItem|Track) => upload.id;

    public suggestTagFn = (query: string) => {
        return this.search.media(query, {types: [TAG_MODEL], limit: 5})
            .pipe(map(response => response.results.tags.map(tag => tag.name)));
    };

    public suggestGenreFn = (query: string) => {
        return this.search.media(query, {types: [GENRE_MODEL], limit: 5})
            .pipe(map(response => response.results.genres.map(genre => genre.name)));
    }
}
