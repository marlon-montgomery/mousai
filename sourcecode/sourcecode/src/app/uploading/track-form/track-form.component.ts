import {
    ChangeDetectionStrategy,
    ChangeDetectorRef,
    Component,
    EventEmitter,
    Input,
    OnChanges,
    OnInit,
    Output,
    SimpleChanges
} from '@angular/core';
import {FormBuilder, FormGroup} from '@angular/forms';
import {BehaviorSubject} from 'rxjs';
import {Tracks} from '../../web-player/tracks/tracks.service';
import {Track} from '../../models/Track';
import {UploadQueueItem} from '@common/uploads/upload-queue/upload-queue-item';
import {DefaultImagePaths} from '../../web-player/default-image-paths.enum';
import {Toast} from '@common/core/ui/toast.service';
import {
    matExpansionAnimations,
    MatExpansionPanelState
} from '@angular/material/expansion';
import {Modal} from '@common/core/ui/dialogs/modal.service';
import {ConfirmModalComponent} from '@common/core/ui/confirm-modal/confirm-modal.component';
import {finalize, map} from 'rxjs/operators';
import {openUploadWindow} from '@common/uploads/utils/open-upload-window';
import {UploadInputTypes} from '@common/uploads/upload-input-config';
import {AudioUploadValidator} from '../../web-player/audio-upload-validator';
import {UploadQueueService} from '@common/uploads/upload-queue/upload-queue.service';
import {WaveformGenerator} from '../../web-player/tracks/waveform/waveform-generator';
import {Router} from '@angular/router';
import {UploadFileResponse} from '@common/uploads/uploads-api.service';
import {Album} from '../../models/Album';
import {Settings} from '@common/core/config/settings.service';
import {isAbsoluteUrl} from '@common/core/utils/is-absolute-url';
import {FileEntry} from '@common/uploads/types/file-entry';
import {randomString} from '@common/core/utils/random-string';
import {scrollInvalidInputIntoView} from '@common/core/utils/scroll-invalid-input-into-view';
import {UploadApiConfig} from '@common/uploads/types/upload-api-config';
import {GENRE_MODEL} from '../../models/Genre';
import {Search} from '../../web-player/search/search.service';
import {BackendErrorResponse} from '@common/core/types/backend-error-response';
import {AppCurrentUser} from '../../app-current-user';
import {Artist} from '../../models/Artist';
import {TAG_MODEL} from '@common/core/types/models/Tag';

export interface ExtractedMetadata {
    title?: string;
    album?: Album;
    album_name?: string;
    artist?: Artist;
    artist_name?: string;
    genres?: string[];
    duration?: number;
    release_date?: string;
    comment?: string;
    image?: FileEntry;
    lyrics?: string;
}

export interface TrackUploadResponse extends UploadFileResponse {
    metadata?: ExtractedMetadata;
}

@Component({
    selector: 'track-form',
    templateUrl: './track-form.component.html',
    styleUrls: ['./track-form.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush,
    animations: [matExpansionAnimations.bodyExpansion]
})
export class TrackFormComponent implements OnInit, OnChanges {
    // track that is being edited
    @Input() track: Track;

    // creating a new track for this upload
    @Input() uploadQueueItem: UploadQueueItem;

    // track will be saved along with this album
    @Input() albumForm: FormGroup;

    // number of this track inside parent album
    @Input() number: number;

    @Output() canceled = new EventEmitter<UploadQueueItem|Track>();
    @Output() saved = new EventEmitter<Track>();

    public errors$ = new BehaviorSubject<{[K in keyof Partial<Track>]: string}>({});
    public defaultImage$ = new BehaviorSubject<string>(DefaultImagePaths.album);
    public loading$ = new BehaviorSubject<boolean>(false);
    public readonly uniqueId = randomString(5);

    public form = this.fb.group({
        id: [null],
        name: [''],
        image: [''],
        description: [''],
        number: [1],
        tags: [[]],
        genres: [[]],
        duration: [null],
        url: [''],
        youtube_id: [''],
        spotify_id: [''],
        spotify_popularity: [''],
        album: [null],
        artists: [[]],
        waveData: [null],
        lyrics: [''],
    });
    public expanded = false;

    constructor(
        private fb: FormBuilder,
        public currentUser: AppCurrentUser,
        private tracks: Tracks,
        private toast: Toast,
        private modal: Modal,
        private audioValidator: AudioUploadValidator,
        private uploadQueue: UploadQueueService,
        private waveGenerator: WaveformGenerator,
        private router: Router,
        public settings: Settings,
        private cd: ChangeDetectorRef,
        private search: Search,
    ) {}

    ngOnChanges(changes: SimpleChanges) {
        if (changes.number && changes.number.currentValue != null) {
            this.form.patchValue({number: changes.number.currentValue});
        }
    }

    ngOnInit() {
        this.expanded = !this.albumForm;

        if (this.track) {
            const formValue = {...this.track};
            formValue.tags = (this.track.tags || []).map(t => t.name) as any;
            formValue.genres = (this.track.genres || []).map(t => t.display_name || t.name) as any;
            this.form.patchValue(formValue);

            if (this.track.album) {
                this.defaultImage$.next(this.track.album.image || DefaultImagePaths.album);
            }
        } else if ( ! this.currentUser.canAttachMusicToAnyArtist()) {
            this.form.get('artists').setValue([
                this.currentUser.get('artists')[0] || this.currentUser.artistPlaceholder(),
            ]);
        }

        if (this.uploadQueueItem) {
            this.uploadQueueItem.uploadedResponse$.subscribe((response: TrackUploadResponse) => {
                this.patchFormUsingFileUpload(response);
            });
        }

        if (this.albumForm) {
            this.albumForm.get('image').valueChanges.subscribe(url => {
                this.defaultImage$.next(url || DefaultImagePaths.album);
            });
        }
    }

    public getPayload(): Partial<Track> {
        const customData = this.uploadQueueItem ? this.uploadQueueItem.customData : {};
        const payload =  {...this.form.value, ...customData};
        payload.artists = payload.artists.map(a => typeof a !== 'number' ? a.id : a);
        return payload;
    }

    public isUploading() {
        return this.uploadQueueItem && !this.uploadQueueItem.completed;
    }

    public submit() {
        if (this.albumForm) return;
        this.loading$.next(true);

        const payload = this.getPayload();

        const request = this.track ?
            this.tracks.update(this.track.id, payload) :
            this.tracks.create(payload);

        request
            .pipe(finalize(() => this.loading$.next(false)))
            .subscribe(response => {
                if (this.uploadQueueItem) {
                    this.uploadQueue.remove(this.uploadQueueItem.id);
                }
                this.toast.open('Track saved.');
                this.form.markAsPristine();
                this.saved.emit(response.track);
            }, (errResponse: BackendErrorResponse) => {
                this.errors$.next(errResponse.errors);
                scrollInvalidInputIntoView(this.errors$.value, `track-form-${this.uniqueId}`);
            });
    }

    public toggleExpandedState() {
        this.expanded = !this.expanded;
    }

    public getExpandedState(): MatExpansionPanelState {
        return this.expanded ? 'expanded' : 'collapsed';
    }

    public maybeCancel() {
        this.modal.show(ConfirmModalComponent, {
            title: 'Remove Track',
            body:  'Are you sure you want to cancel the upload and remove this track?',
            ok:    'Remove'
        }).beforeClosed().subscribe(confirmed => {
            if ( ! confirmed) return;
            if (this.uploadQueueItem) {
                this.uploadQueue.remove(this.uploadQueueItem.id);
                this.canceled.emit(this.uploadQueueItem);
                this.toast.open('Upload canceled.');
            } else if (this.track) {
                this.tracks.delete([this.track.id]).subscribe(() => {
                    this.canceled.emit(this.track);
                    this.toast.open('Track deleted.');
                });
            }
        });
    }

    public openUploadMusicModal() {
        const params = {
            uri: 'uploads',
            validator: this.audioValidator,
            httpParams: {diskPrefix: 'track_media', disk: 'public'},
            willProcessFiles: true,
        } as UploadApiConfig;
        openUploadWindow({types: [UploadInputTypes.audio, UploadInputTypes.video]}).then(uploadedFiles => {
            if ( ! uploadedFiles) return;
            // if this track form is already attached to existing upload queue item
            // replace that item in queue service instead of creating a new item
            const replacements = this.uploadQueueItem ? {[this.uploadQueueItem.id]: uploadedFiles[0]} : uploadedFiles;
            this.uploadQueue.start(replacements, params).subscribe(response => {
                const queueItem = this.uploadQueue.find(response.queueItemId);
                this.waveGenerator.generate(queueItem.uploadedFile.native).then(waveData => {
                    this.form.patchValue({waveData});
                    queueItem.finishProcessing();
                });
                this.patchFormUsingFileUpload(response);
                this.toast.open('Track uploaded.');
            }, () => this.toast.open('Could not upload track'));
            // make sure progress bar is shown if we're editing track from admin
            if ( ! this.uploadQueueItem) {
                this.uploadQueueItem = this.uploadQueue.uploads$.value[0];
                this.cd.markForCheck();
            }
        });
    }

    private patchFormUsingFileUpload(response: TrackUploadResponse) {
        const values: {[K in keyof Partial<Track>]: any} & {lyrics?: string} = {
            name: response.metadata.title,
            duration: response.metadata.duration,
            url: response.fileEntry.url,
            genres: response.metadata.genres || [],
            description: response.metadata.comment,
            lyrics: response.metadata.lyrics,
        };
        if (response.metadata.album) {
            values.album = response.metadata.album;
        }
        if (response.metadata.artist) {
            values.artists = [response.metadata.artist];

            // set artist on album, if does not already have one
            if (this.albumForm && ! this.albumForm.value.artist) {
                this.albumForm.patchValue({artist: response.metadata.artist});
            }
        }
        if (response.metadata.image) {
            values.image = response.metadata.image.url;

            // set image on album, if does not already have one
            if (this.albumForm && ! this.albumForm.value.image) {
                this.albumForm.patchValue({image: response.metadata.image.url});
            }
        }
        if (response.metadata.release_date && this.albumForm && !this.albumForm.value.release_date) {
            this.albumForm.patchValue({release_date: response.metadata.release_date});
        }
        this.form.patchValue(values);
    }

    public insideAdmin(): boolean {
        return this.router.url.indexOf('admin') > -1;
    }

    public shouldShowDurationField() {
        const trackUrl = this.form.get('url').value;
        return !trackUrl || isAbsoluteUrl(trackUrl);
    }

    public suggestTagFn = (query: string) => {
        return this.search.media(query, {types: [TAG_MODEL], limit: 5})
            .pipe(map(response => response.results.tags.map(tag => tag.name)));
    }

    public suggestGenreFn = (query: string) => {
        return this.search.media(query, {types: [GENRE_MODEL], limit: 5})
            .pipe(map(response => response.results.genres.map(genre => genre.name)));
    }
}
