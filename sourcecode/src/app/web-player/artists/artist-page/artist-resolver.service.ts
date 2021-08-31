import {Injectable} from '@angular/core';
import {
    ActivatedRouteSnapshot,
    Resolve,
    Router,
    RouterStateSnapshot
} from '@angular/router';
import {Artists, GetArtistResponse} from '../artists.service';
import {Player} from '../../player/player.service';
import {WebPlayerState} from '../../web-player-state.service';
import {catchError, mergeMap} from 'rxjs/operators';
import {EMPTY, of} from 'rxjs';
import {BackendResponse} from '@common/core/types/backend-response';

@Injectable({
    providedIn: 'root'
})
export class ArtistResolver implements Resolve<GetArtistResponse> {
    constructor(
        private artists: Artists,
        private player: Player,
        private state: WebPlayerState,
        private router: Router,
    ) {}

    resolve(route: ActivatedRouteSnapshot, state: RouterStateSnapshot): BackendResponse<GetArtistResponse> {
        const params = {
            autoUpdate: true,
            defaultRelations: true,
        };
        this.state.loading = true;
        const id = +route.paramMap.get('id');
        return this.artists.get(id, params).pipe(
            catchError(() => {
                this.state.loading = false;
                this.router.navigate(['/']);
                return EMPTY;
            }),
            mergeMap(response => {
                this.state.loading = false;
                if (response.artist) {
                    return of(response);
                } else {
                    this.router.navigate(['/']);
                    return EMPTY;
                }
            })
        );
    }
}
