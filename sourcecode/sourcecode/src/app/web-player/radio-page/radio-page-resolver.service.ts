import {Injectable} from '@angular/core';
import {
    ActivatedRouteSnapshot,
    Resolve,
    Router,
    RouterStateSnapshot
} from '@angular/router';
import {WebPlayerState} from '../web-player-state.service';
import {Track} from '../../models/Track';
import {Artist} from '../../models/Artist';
import {AppHttpClient} from '@common/core/http/app-http-client.service';
import {Genre} from '../../models/Genre';

@Injectable({
    providedIn: 'root'
})
export class RadioPageResolver implements Resolve<{recommendations: Track[], seed: Artist|Track|Genre}> {

    constructor(
        private http: AppHttpClient,
        private router: Router,
        private state: WebPlayerState
    ) {}

    resolve(route: ActivatedRouteSnapshot, state: RouterStateSnapshot): Promise<{recommendations: Track[], seed: Artist|Track|Genre}> {
        this.state.loading = true;
        const id = +route.paramMap.get('id');
        const type = route.paramMap.get('type');


        return this.http.get(`radio/${type}/${id}`).toPromise().then(response => {
            this.state.loading = false;
            if (response) {
                return response;
            } else {
                this.router.navigate(['/']);
                return null;
            }
        }).catch(() => {
            this.state.loading = false;
            this.router.navigate(['/']);
        }) as any;
    }
}
