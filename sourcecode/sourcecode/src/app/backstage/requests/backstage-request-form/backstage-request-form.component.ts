import {ChangeDetectionStrategy, Component} from '@angular/core';
import {BackstagRequestService} from '../backstag-request.service';
import {FormBuilder} from '@angular/forms';
import {Settings} from '@common/core/config/settings.service';
import {CurrentUser} from '@common/auth/current-user';
import {SocialAuthService} from '@common/auth/social-auth.service';
import {UploadedFile} from '@common/uploads/uploaded-file';
import {BehaviorSubject} from 'rxjs';
import {UploadQueueService} from '@common/uploads/upload-queue/upload-queue.service';
import {UploadApiConfig} from '@common/uploads/types/upload-api-config';
import {ImageUploadValidator} from '../../../web-player/image-upload-validator';
import {FileEntry} from '@common/uploads/types/file-entry';
import {ExternalSocialProfile} from '@common/auth/external-social-profile';
import {BackendErrorResponse} from '@common/core/types/backend-error-response';
import {finalize} from 'rxjs/operators';
import {ActivatedRoute, Router} from '@angular/router';
import {BackstageRequestType} from '../backstage-request-type';
import {AppCurrentUser} from '../../../app-current-user';

@Component({
    selector: 'backstage-request-form',
    templateUrl: './backstage-request-form.component.html',
    styleUrls: ['./backstage-request-form.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush
})
export class BackstageRequestFormComponent {
    public uploadedPassportEntry$ = new BehaviorSubject<FileEntry>(null);
    public socialProfiles$ = new BehaviorSubject<{[key: string]: ExternalSocialProfile}>(null);
    public errors$ = new BehaviorSubject<object>({});
    public loading$ = new BehaviorSubject(false);
    public requestType$ = new BehaviorSubject<BackstageRequestType>(null);
    public form = this.fb.group({
        artist: [],
        artist_name: [this.currentUser.get('display_name')],
        image: [this.currentUser.primaryArtist()?.image_small || this.currentUser.get('avatar')],
        first_name: [this.currentUser.get('first_name')],
        last_name: [this.currentUser.get('last_name')],
        role: [],
        company: [''],
    });

    constructor(
        private backstage: BackstagRequestService,
        private fb: FormBuilder,
        public settings: Settings,
        private currentUser: AppCurrentUser,
        private socialAuth: SocialAuthService,
        private uploadQueue: UploadQueueService,
        private imageValidator: ImageUploadValidator,
        private router: Router,
        private route: ActivatedRoute,
    ) {
        this.requestType$.next(this.route.routeConfig.path.replace('requests/', '') as BackstageRequestType);
        if (this.requestType$.value === BackstageRequestType.verifyArtist) {
            this.form.get('artist').setValue(this.currentUser.primaryArtist());
            this.form.get('artist').disable();
        }
        if (this.requestType$.value === BackstageRequestType.becomeArtist) {
            this.form.get('artist').setValue(this.currentUser.artistPlaceholder());
            this.form.get('artist').disable();
        }
        if (this.requestType$.value !== BackstageRequestType.claimArtist) {
            this.form.get('role').setValue('artist');
        }
        if (this.requestType$.value !== BackstageRequestType.becomeArtist) {
            this.form.get('image').disable();
        }
    }

    public requestAccess() {
        this.loading$.next(true);
        const payload = {
            artist_name: this.form.value.artist_name,
            artist_id: this.form.value.artist?.id,
            type: this.requestType$.value,
            data: {
                ...this.form.value,
                passportScanEntryId: this.uploadedPassportEntry$.value?.id,
                socialProfiles: this.socialProfiles$.value,
            }
        };
        this.backstage.submitRequest(payload)
            .pipe(finalize(() => this.loading$.next(false)))
            .subscribe(response => {
                this.router.navigate(['/backstage/requests', response.request.id, 'request-submitted'], {replaceUrl: true});
            }, (err: BackendErrorResponse) => this.errors$.next(err.errors));
    }

    public retrieveSocialProfile(serviceName: string) {
        this.socialAuth.retrieveProfile(serviceName).then(profile => {
            this.socialProfiles$.next({
                ...this.socialProfiles$.value,
                [serviceName]: profile,
            });
        });
    }

    public uploadPassportScan(files: UploadedFile[]) {
        const params = {
            uri: 'uploads/images',
            httpParams: {diskPrefix: 'test', disk: 'private'},
            validator: this.imageValidator
        } as UploadApiConfig;
        this.uploadQueue.start(files, params).subscribe(response => {
            this.uploadedPassportEntry$.next(response.fileEntry);
        });
    }

    public removePassportUpload() {
        this.uploadedPassportEntry$.next(null);
    }

    public removeSocialProfile(serviceName: string) {
        const profiles = {...this.socialProfiles$.value};
        delete profiles[serviceName];
        this.socialProfiles$.next(profiles);
    }
}
