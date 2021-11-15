import {Injectable} from '@angular/core';
import {ActivatedRouteSnapshot, Resolve, Router, RouterStateSnapshot} from '@angular/router';
import {GetPlaylistResponse, Playlists} from '../playlists.service';
import {Player} from '../../player/player.service';
import {Settings} from '@common/core/config/settings.service';
import {WebPlayerState} from '../../web-player-state.service';
import {WebPlayerImagesService} from '../../web-player-images.service';
import {catchError, mergeMap} from 'rxjs/operators';
import {EMPTY, of} from 'rxjs';
import {BackendResponse} from '@common/core/types/backend-response';

@Injectable({
    providedIn: 'root'
})
export class PlaylistResolver implements Resolve<GetPlaylistResponse> {

    constructor(
        private playlists: Playlists,
        private player: Player,
        private settings: Settings,
        private router: Router,
        private state: WebPlayerState,
        public images: WebPlayerImagesService,
    ) {}

    resolve(route: ActivatedRouteSnapshot, state: RouterStateSnapshot): BackendResponse<GetPlaylistResponse> {
        this.state.loading = true;
        const id = +route.paramMap.get('id');
        return this.playlists.get(id).pipe(
            catchError(() => {
                this.state.loading = false;
                this.router.navigate(['/']);
                return EMPTY;
            }),
            mergeMap(response => {
                this.state.loading = false;
                if (response.playlist) {
                    return of(response);
                } else {
                    this.router.navigate(['/']);
                    return EMPTY;
                }
            })
        );
    }
}
