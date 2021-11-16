import {Injectable} from '@angular/core';
import {
    ActivatedRouteSnapshot,
    Resolve,
    Router,
    RouterStateSnapshot
} from '@angular/router';
import {Artists, GetArtistResponse} from '../../../web-player/artists/artists.service';
import {catchError, mergeMap} from 'rxjs/operators';
import {EMPTY, of} from 'rxjs';
import {BackendResponse} from '@common/core/types/backend-response';

@Injectable({
    providedIn: 'root',
})
export class EditArtistPageResolver implements Resolve<GetArtistResponse> {

    constructor(
        private artists: Artists,
        private router: Router
    ) {}

    resolve(route: ActivatedRouteSnapshot, state: RouterStateSnapshot): BackendResponse<GetArtistResponse> {
        const params = {
            with: ['simplifiedAlbums', 'genres', 'profile'],
            albumsPerPage: 50,
        };
        const id = +route.paramMap.get('id');
        return this.artists.get(id, params).pipe(
            catchError(() => {
                this.router.navigate(['/']);
                return EMPTY;
            }),
            mergeMap(response => {
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
