import {Injectable} from '@angular/core';
import {Track, TRACK_MODEL} from '../../../models/Track';
import {AppHttpClient} from '@common/core/http/app-http-client.service';
import {Artist, ARTIST_MODEL} from '../../../models/Artist';
import {Album, ALBUM_MODEL} from '../../../models/Album';

export type Likeable = Artist|Album|Track;
interface AllLikes {
    [key: string]: {[key: number]: true};
}

@Injectable({
    providedIn: 'root'
})
export class UserLibrary {
    private likes: AllLikes = {
        [TRACK_MODEL]: {},
        [ALBUM_MODEL]: {},
        [ARTIST_MODEL]: {},
    };

    constructor(private http: AppHttpClient) {}

    public setLikes(likeables: AllLikes) {
        this.likes = {...this.likes, ...likeables};
    }

    public add(likeables: Likeable[]): Promise<Likeable[]> {
        return new Promise((resolve, reject) => {
            const payload = likeables
                .filter(likeable => !this.has(likeable))
                .map(likeable => {
                    return {likeable_id: likeable.id, likeable_type: likeable.model_type};
                });
            this.http.post('users/me/add-to-library', {likeables: payload}).subscribe(() => {
                payload.forEach(like => {
                    if ( ! this.likes[like.likeable_type]) {
                        this.likes[like.likeable_type] = {};
                    }
                    this.likes[like.likeable_type][like.likeable_id] = true;
                });
                resolve(likeables);
            }, () => reject());
        });
    }

    public remove(likeables: Likeable[]) {
        return new Promise(((resolve, reject) => {
            const payload = likeables.map(likeable => {
                return {likeable_id: likeable.id, likeable_type: likeable.model_type};
            });
            this.http.delete('users/me/remove-from-library', {likeables: payload}).subscribe(() => {
                payload.forEach(like => {
                    delete this.likes[like.likeable_type][like.likeable_id];
                });
                resolve(likeables);
            }, () => reject());
        }));
    }

    public has(likeable: Likeable): boolean {
        return this.likes[likeable.model_type] &&
            this.likes[likeable.model_type][likeable.id];
    }

    public count(modelType: string): number {
        return Object.keys(this.likes[modelType]).length;
    }
}
