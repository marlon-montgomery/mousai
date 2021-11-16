import {
    ChangeDetectionStrategy,
    Component,
    ElementRef,
    QueryList,
    ViewChild,
    ViewChildren
} from '@angular/core';
import {Tracks} from '../../web-player/tracks/tracks.service';
import {CurrentUser} from '@common/auth/current-user';
import {UploadedFile} from '@common/uploads/uploaded-file';
import {UploadQueueService} from '@common/uploads/upload-queue/upload-queue.service';
import {Settings} from '@common/core/config/settings.service';
import {Toast} from '@common/core/ui/toast.service';
import {AudioUploadValidator} from '../../web-player/audio-upload-validator';
import {BehaviorSubject} from 'rxjs';
import {UploadQueueItem} from '@common/uploads/upload-queue/upload-queue-item';
import {UploadInputConfig, UploadInputTypes} from '@common/uploads/upload-input-config';
import {Track} from '../../models/Track';
import {TrackFormComponent} from '../track-form/track-form.component';
import {Album} from '../../models/Album';
import {WaveformGenerator} from '../../web-player/tracks/waveform/waveform-generator';
import {UploadApiConfig} from '@common/uploads/types/upload-api-config';

@Component({
    selector: 'upload-page',
    templateUrl: './upload-page.component.html',
    styleUrls: ['./upload-page.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush,
    providers: [UploadQueueService],
})
export class UploadPageComponent {
    @ViewChild('clickMatButton', {read: ElementRef, static: true}) clickButton: ElementRef<HTMLButtonElement>;
    @ViewChildren(TrackFormComponent) trackForms: QueryList<TrackFormComponent>;
    public errors$ = new BehaviorSubject<{[key: string]: {[K in keyof Partial<Track>]: string}}>({});
    public uploadConfig: UploadInputConfig = {
        types: [UploadInputTypes.video, UploadInputTypes.audio],
        multiple: true
    };
    public savedMedia$ = new BehaviorSubject<(Track|Album)[]>([]);
    public createAlbum$ = new BehaviorSubject<boolean>(false);

    constructor(
        private track: Tracks,
        public currentUser: CurrentUser,
        public uploadQueue: UploadQueueService,
        public settings: Settings,
        protected tracks: Tracks,
        private toast: Toast,
        private audioValidator: AudioUploadValidator,
        private waveGenerator: WaveformGenerator,
    ) {}

    public uploadTracks(files: UploadedFile[]) {
        const params = {
            uri: 'uploads',
            httpParams: {diskPrefix: 'track_media', disk: 'public'},
            validator: this.audioValidator,
            willProcessFiles: true,
        } as UploadApiConfig;
        this.uploadQueue.start(files, params).subscribe(response => {
            const queueItem = this.uploadQueue.find(response.queueItemId);
            this.waveGenerator.generate(queueItem.uploadedFile.native).then(waveData => {
                queueItem.customData = {waveData};
                queueItem.finishProcessing();
            });
        });
    }

    public addSavedMedia(newMedia: Track|Album) {
        this.savedMedia$.next([...this.savedMedia$.value, newMedia]);
    }

    public trackByFn = (i: number, upload: UploadQueueItem) => upload.id;
}
