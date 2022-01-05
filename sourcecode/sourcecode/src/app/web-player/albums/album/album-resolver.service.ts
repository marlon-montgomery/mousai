import {Injectable} from '@angular/core';
import {
    ActivatedRouteSnapshot,
    Resolve,
    Router,
    RouterStateSnapshot
} from '@angular/router';
import {Album} from '../../../models/Album';
import {Albums} from '../albums.service';
import {WebPlayerState} from '../../web-player-state.service';
import {catchError, mergeMap} from 'rxjs/operators';
import {EMPTY, of} from 'rxjs';
import {BackendResponse} from '@common/core/types/backend-response';

@Injectable({
    providedIn: 'root'
})
export class AlbumResolver implements Resolve<BackendResponse<{album: Album}>> {
    constructor(
        private albums: Albums,
        private router: Router,
        private state: WebPlayerState,
    ) {}

    resolve(route: ActivatedRouteSnapshot, state: RouterStateSnapshot): BackendResponse<{album: Album}> {
        this.state.loading = true;
        const id = +route.paramMap.get('id');
        return this.albums.get(id, {autoUpdate: true, defaultRelations: true}).pipe(
            catchError(() => {
                this.state.loading = false;
                this.router.navigate(['/']);
                return EMPTY;
            }),
            mergeMap(response => {
                this.state.loading = false;
                if (response.album) {
                    return of(response);
                } else {
                    this.router.navigate(['/']);
                    return EMPTY;
                }
            })
        );
    }
}
