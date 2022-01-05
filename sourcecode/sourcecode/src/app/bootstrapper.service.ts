import {Injectable} from '@angular/core';
import {UserLibrary} from './web-player/users/user-library/user-library.service';
import {UserPlaylists} from './web-player/playlists/user-playlists.service';
import {Bootstrapper} from '@common/core/bootstrapper.service';

@Injectable({
    providedIn: 'root'
})
export class BeMusicBootstrapper extends Bootstrapper {
    protected handleData(encodedData: string) {
        const data = super.handleData(encodedData);

        // set user library
        this.injector.get(UserLibrary).setLikes(data.likes);

        // set user playlists
        this.injector.get(UserPlaylists).set(data.playlists);

        return data;
    }
}
