import {Injectable} from '@angular/core';
import {ActivatedRouteSnapshot, Resolve, Router, RouterStateSnapshot} from '@angular/router';
import {catchError, mergeMap} from 'rxjs/operators';
import {EMPTY, of} from 'rxjs';
import {BackendResponse} from '@common/core/types/backend-response';
import {Channel} from '../channel';
import {ChannelService} from '../channel.service';
import {Settings} from '@common/core/config/settings.service';
import {CurrentUser} from '@common/auth/current-user';
import {BackendErrorResponse} from '@common/core/types/backend-error-response';

@Injectable({
    providedIn: 'root'
})
export class ChannelResolverService implements Resolve<BackendResponse<{channel: Channel}>> {
    constructor(
        private router: Router,
        private channels: ChannelService,
        protected settings: Settings,
        protected currentUser: CurrentUser,
    ) {}

    resolve(route: ActivatedRouteSnapshot, state: RouterStateSnapshot): BackendResponse<{channel: Channel}> {
        const idOrSlug = +route.params.id || route.params.slug || route.data.id || route.data.channelName;
        if ( ! idOrSlug) {
            return of(null);
        }
        return this.channels.get(idOrSlug, {filter: route.params.filter, forAdmin: route.data.forAdmin}, {suppressAuthToast: true}).pipe(
            catchError((e: BackendErrorResponse) => {
                this.redirectOnResolveFail(route, state, e.status);
                return EMPTY;
            }),
            mergeMap(response => {
                if (response) {
                    return of(response);
                } else {
                    this.redirectOnResolveFail(route, state);
                    return EMPTY;
                }
            })
        );
    }

    private redirectOnResolveFail(route: ActivatedRouteSnapshot, state: RouterStateSnapshot, status?: number) {
        let redirectUri;
        if (status === 403 && this.settings.get('billing.enable')) {
            redirectUri = this.currentUser.isLoggedIn() ? '/billing/upgrade' : '/billing/pricing';
        } else {
            redirectUri = route.data.failRedirectUri || '/';
        }

        if (redirectUri && state.url !== redirectUri) {
            return this.router.navigate([redirectUri]);
        }
    }
}

