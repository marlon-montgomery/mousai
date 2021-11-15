import {Injectable} from '@angular/core';
import {ActivatedRouteSnapshot, Resolve, Router, RouterStateSnapshot} from '@angular/router';
import {WebPlayerState} from '../../web-player-state.service';
import {catchError, mergeMap} from 'rxjs/operators';
import {EMPTY, of} from 'rxjs';
import {BackendResponse} from '@common/core/types/backend-response';
import {GetProfileResponse, UserProfileService} from '../user-profile.service';

@Injectable({
    providedIn: 'root'
})
export class UserProfilePageResolver implements Resolve<BackendResponse<GetProfileResponse>> {
    constructor(
        private profiles: UserProfileService,
        private router: Router,
        private state: WebPlayerState
    ) {}

    resolve(route: ActivatedRouteSnapshot, state: RouterStateSnapshot): BackendResponse<GetProfileResponse> {
        this.state.loading = true;
        const id = +route.paramMap.get('id');

        return this.profiles.get(id).pipe(
            catchError(() => {
                this.state.loading = false;
                this.router.navigate(['/']);
                return EMPTY;
            }),
            mergeMap(response => {
                this.state.loading = false;
                if (response.user) {
                    return of(response);
                } else {
                    this.router.navigate(['/']);
                    return EMPTY;
                }
            })
        );
    }
}
