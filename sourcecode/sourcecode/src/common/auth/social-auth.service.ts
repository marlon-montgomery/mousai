import {Injectable} from '@angular/core';
import {Router} from '@angular/router';
import {MatDialogRef} from '@angular/material/dialog';
import {RequestExtraCredentialsModalComponent} from './request-extra-credentials-modal/request-extra-credentials-modal.component';
import {CurrentUser} from './current-user';
import {Settings} from '../core/config/settings.service';
import {Toast} from '../core/ui/toast.service';
import {Bootstrapper} from '../core/bootstrapper.service';
import {AuthService} from './auth.service';
import {Translations} from '../core/translations/translations.service';
import {Modal} from '../core/ui/dialogs/modal.service';
import {User} from '../core/types/models/User';
import {AppHttpClient} from '../core/http/app-http-client.service';
import {ExternalSocialProfile} from '@common/auth/external-social-profile';
import {identityLogin} from './bitclout';

@Injectable({
    providedIn: 'root',
})
export class SocialAuthService {

    /**
     * Instance of extraCredentialsModal.
     */
    private extraCredentialsModal: MatDialogRef<RequestExtraCredentialsModalComponent>;

    /**
     * Social login popup window height.
     */
    private windowHeight = 550;

    /**
     * Social login popup window width.
     */
    private windowWidth = 650;

    /**
     * Resolve for latest social login or connect call.
     */
    private resolve: (value: any) => void;

    constructor(
        protected httpClient: AppHttpClient,
        public currentUser: CurrentUser,
        protected router: Router,
        public settings: Settings,
        protected toast: Toast,
        protected auth: AuthService,
        protected i18n: Translations,
        protected modal: Modal,
        private bootstrapper: Bootstrapper,
    ) {
        this.listenForMessageFromPopup();
    }

    /**
     * Log user in with specified social account.
     */
    public loginWith(serviceName: string): Promise<User> {
        return this.openNewSocialAuthWindow('secure/auth/social/' + serviceName + '/login');
    }

    public async loginWithBitclout() {
        try {
            const user = await identityLogin(4);

            await this.httpClient.post('secure/auth/social/bitclout', {
                publicKey: user.publicKey,
                jwt: user.jwt,
            })
                .subscribe(({data}) => {
                    this.bootstrapper.bootstrap(data);
                    this.router.navigate([this.auth.getRedirectUri()]);
                }, () => null);
        } catch (err) {
            this.toast.open('Try later', {duration: 6000});
        }
    }

    /**
     * Connect specified social account to current user.
     */
    public connect(serviceName: string): Promise<User> {
        return this.openNewSocialAuthWindow('secure/auth/social/' + serviceName + '/connect');
    }

    public retrieveProfile(serviceName: string): Promise<ExternalSocialProfile> {
        return this.openNewSocialAuthWindow('secure/auth/social/' + serviceName + '/retrieve-profile');
    }

    /**
     * Disconnect specified social account from current user.
     */
    public disconnect(serviceName: string) {
        return this.httpClient.post('auth/social/' + serviceName + '/disconnect');
    }

    /**
     * Handle social login callback, based on returned status.
     */
    public socialLoginCallback(status: string, data = null) {
        if (!status) return;
        switch (status.toUpperCase()) {
            case 'SUCCESS':
                this.currentUser.assignCurrent(data.user);
                this.router.navigate([this.auth.getRedirectUri()]);
                break;
            case 'SUCCESS_CONNECTED':
                if (this.resolve) this.resolve(data.user);
                break;
            case 'SUCCESS_PROFILE_RETRIEVE':
                if (this.resolve) this.resolve(data.profile);
                break;
            case 'ALREADY_LOGGED_IN':
                this.router.navigate([this.auth.getRedirectUri()]);
                break;
            case 'REQUEST_EXTRA_CREDENTIALS':
                this.showRequestExtraCredentialsModal({credentials: data});
                break;
            case 'ERROR':
                const message = data ? data : this.i18n.t('An error occurred. Please try again later');
                this.toast.open(message, {duration: 6000});
        }
    }

    /**
     * Open extra credentials modal and subscribe to modal events.
     */
    public showRequestExtraCredentialsModal(config: object) {
        this.extraCredentialsModal = this.modal.open(RequestExtraCredentialsModalComponent, config);
        this.extraCredentialsModal.componentInstance.onSubmit$.subscribe(credentials => {
            if (!credentials) return;
            this.sendExtraCredentialsToBackend(credentials);
        });
    }

    /**
     * Send specified credentials to backend and handle success/error.
     */
    public sendExtraCredentialsToBackend(params: object) {
        this.httpClient.post('auth/social/extra-credentials', params).subscribe(({data}) => {
            this.currentUser.assignCurrent(data);
            this.extraCredentialsModal.close();
            this.router.navigate([this.auth.getRedirectUri()]).then(() => {
                this.toast.open('Accounts connected');
            });
        }, this.extraCredentialsModal.componentInstance.handleErrors.bind(this.extraCredentialsModal.componentInstance));
    }

    /**
     * Open new browser window with given url.
     */
    private openNewSocialAuthWindow(url: string): Promise<any> {
        const left = (screen.width / 2) - (this.windowWidth / 2);
        const top = (screen.height / 2) - (this.windowHeight / 2);

        return new Promise(resolve => {
            this.resolve = resolve;
            window.open(
                url,
                'Authenticate Account',
                'menubar=0, location=0, toolbar=0, titlebar=0, status=0, scrollbars=1, width=' + this.windowWidth
                + ', height=' + this.windowHeight + ', ' + 'left=' + left + ', top=' + top
            );
        });
    }

    /**
     * Listen for "message" event from social login popup
     * window and call "socialLoginCallback" once received
     */
    private listenForMessageFromPopup() {
        window.addEventListener('message', e => {
            if (e.data.type !== 'social-auth' || this.settings.getBaseUrl().indexOf(e.origin) === -1) return;
            this.socialLoginCallback(e.data.status, e.data.callbackData);
        }, false);
    }
}
