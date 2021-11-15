import { ChangeDetectionStrategy, Component, Inject, OnInit } from '@angular/core';
import {BehaviorSubject} from 'rxjs';
import {MAT_DIALOG_DATA, MatDialogRef} from '@angular/material/dialog';
import {Toast} from '@common/core/ui/toast.service';
import {FormControl, FormGroup} from '@angular/forms';
import {CustomDomainService} from '../custom-domain.service';
import {CustomDomain} from '../custom-domain';
import {finalize} from 'rxjs/operators';
import {Settings} from '@common/core/config/settings.service';
import {Router} from '@angular/router';
import {BackendErrorResponse} from '@common/core/types/backend-error-response';
import { CurrentUser } from '@common/auth/current-user';

interface CrupdateCustomDomainModalData {
    domain: CustomDomain;
    resourceName: string;
}

type FailReason = 'serverNotConfigured' | 'dnsNotSetup';

enum Steps {
    Host = 1,
    Info = 2,
    Validate = 3,
    Finalize = 4,
}

@Component({
    selector: 'crupdate-custom-domain-modal',
    templateUrl: './crupdate-custom-domain-modal.component.html',
    styleUrls: ['./crupdate-custom-domain-modal.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class CrupdateCustomDomainModalComponent implements OnInit {
    Steps = Steps;
    serverIp: string;
    currentStep$ = new BehaviorSubject<number>(1);
    loading$ = new BehaviorSubject(false);
    disabled$ = new BehaviorSubject(false);
    updating$ = new BehaviorSubject(false);
    errors$ = new BehaviorSubject<{host?: string}>({});
    validationFailReason$ = new BehaviorSubject<FailReason>(null);
    isSubdomain$ = new BehaviorSubject<boolean>(false);
    form = new FormGroup({
        host: new FormControl(),
        global: new FormControl(false),
    });
    currentUserIsAdmin: boolean;

    constructor(
        private dialogRef: MatDialogRef<CrupdateCustomDomainModalComponent>,
        @Inject(MAT_DIALOG_DATA) public data: CrupdateCustomDomainModalData,
        private customDomains: CustomDomainService,
        private toast: Toast,
        private settings: Settings,
        private router: Router,
        private currentUser: CurrentUser
    ) {
        this.updating$.next(!!data.domain);
        this.currentUserIsAdmin = this.currentUser.isAdmin();
        if (data.domain) {
            this.form.patchValue(data.domain);
        }
    }

    ngOnInit() {
        this.form.get('host').valueChanges.subscribe(value => {
            this.isSubdomain$.next((value.replace('www.', '').match(/\./g) || []).length > 1);
        });
    }

    private connectDomain() {
        this.loading$.next(true);
        const request = this.updating$.value
            ? this.customDomains.update(this.data.domain.id, this.form.value)
            : this.customDomains.create(this.form.value);

        request.pipe(finalize(() => this.loading$.next(false))).subscribe(
            response => {
                this.toast.open('Domain connected');
                this.close(response.domain);
            },
            (errResponse: BackendErrorResponse) => {
                this.errors$.next(errResponse.errors);
            }
        );
    }

    public validateDnsForDomain() {
        this.disabled$.next(true);
        this.loading$.next(true);
        this.customDomains
            .validate(this.form.value.host)
            .pipe(finalize(() => this.loading$.next(false)))
            .subscribe(
                response => {
                    if (response && response.result === 'connected') {
                        this.nextStep();
                    }
                },
                (
                    errResponse: BackendErrorResponse & {
                        failReason?: FailReason;
                    }
                ) => {
                    this.validationFailReason$.next(errResponse.failReason);
                }
            );
    }

    private authorizeCrupdate() {
        this.loading$.next(true);
        const payload = {...this.form.value};
        if (this.data.domain) {
            payload.domainId = this.data.domain.id;
        }
        this.customDomains
            .authorizeCrupdate(payload)
            .pipe(finalize(() => this.loading$.next(false)))
            .subscribe(
                response => {
                    this.serverIp = response.serverIp;
                    this.nextStep(true);
                },
                (errResponse: BackendErrorResponse) =>
                    this.errors$.next(errResponse.errors)
            );
    }

    public close(domain?: CustomDomain) {
        this.dialogRef.close(domain);
    }

    public previousStep() {
        if (this.currentStep$.value > Steps.Host) {
            this.currentStep$.next(this.currentStep$.value - 1);
        }
    }

    public nextStep(skipAuthorize = false) {
        // run authorization before asking user to change their DNS
        // in case they don't have permissions to create new domains
        if (this.currentStep$.value === Steps.Host && !skipAuthorize) {
            return this.authorizeCrupdate();
        }

        this.currentStep$.next(this.currentStep$.value + 1);
        if (this.currentStep$.value === Steps.Validate) {
            // host did not change, no need to re-validate
            if (
                this.data.domain &&
                this.form.value.host === this.data.domain.host
            ) {
                this.connectDomain();
            } else {
                this.validateDnsForDomain();
            }
        } else if (this.currentStep$.value === Steps.Finalize) {
            this.connectDomain();
        } else {
            //
        }
    }

    public baseUrl(): string {
        return this.settings.getBaseUrl().replace(/\/$/, '');
    }

    public insideAdmin(): boolean {
        return this.router.url.indexOf('admin') > -1;
    }
}
