import {Injectable} from '@angular/core';
import {
    ActivatedRouteSnapshot,
    Resolve,
    Router,
    RouterStateSnapshot
} from '@angular/router';
import {catchError, mergeMap} from 'rxjs/operators';
import {EMPTY, of} from 'rxjs';
import {BackendResponse} from '@common/core/types/backend-response';
import {Albums} from '../../../web-player/albums/albums.service';

@Injectable({
    providedIn: 'root',
})
export class CrupdateAlbumPageResolverService implements Resolve<any> {

    constructor(
        private albums: Albums,
        private router: Router
    ) {}

    resolve(route: ActivatedRouteSnapshot, state: RouterStateSnapshot): BackendResponse<any> {
        const params = {
            with: ['tags', 'genres', 'artists', 'fullTracks'],
            albumsPerPage: 50,
            forEditing: true,
        };
        const id = +route.paramMap.get('id');
        return this.albums.get(id, params).pipe(
            catchError(() => {
                this.router.navigate(['/']);
                return EMPTY;
            }),
            mergeMap(response => {
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
