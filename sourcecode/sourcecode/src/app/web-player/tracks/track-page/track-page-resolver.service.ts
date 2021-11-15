import {Injectable} from '@angular/core';
import {
    ActivatedRouteSnapshot,
    Resolve,
    Router,
    RouterStateSnapshot
} from '@angular/router';
import {WebPlayerState} from '../../web-player-state.service';
import {Tracks} from '../tracks.service';
import {Track} from '../../../models/Track';
import {catchError, mergeMap} from 'rxjs/operators';
import {EMPTY, of} from 'rxjs';
import {BackendResponse} from '@common/core/types/backend-response';

@Injectable({
    providedIn: 'root'
})
export class TrackPageResolver implements Resolve<BackendResponse<{track: Track}>> {
    constructor(
        private tracks: Tracks,
        private router: Router,
        private state: WebPlayerState,
    ) {}

    resolve(route: ActivatedRouteSnapshot, state: RouterStateSnapshot): BackendResponse<{track: Track}> {
        this.state.loading = true;
        const id = +route.paramMap.get('id');
        return this.tracks.get(id, {defaultRelations: true, forEditing: true}).pipe(
            catchError(() => {
                this.state.loading = false;
                this.router.navigate(['/']);
                return EMPTY;
            }),
            mergeMap(response => {
                this.state.loading = false;

                if (response.track) {
                    return of(response);
                } else {
                    this.router.navigate(['/']);
                    return EMPTY;
                }
            })
        );
    }
}
