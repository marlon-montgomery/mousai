import {Injectable} from '@angular/core';
import {Artist} from '../../../models/Artist';
import {PaginationResponse} from '@common/core/types/pagination/pagination-response';
import {Album} from '../../../models/Album';
import {BehaviorSubject} from 'rxjs';
import {queueId} from '../../player/queue-id';
import linkifyStr from 'linkifyjs/string';
import {ProfileImage} from '../../../models/profile-image';
import {Settings} from '@common/core/config/settings.service';

@Injectable({
    providedIn: 'root'
})
export class ArtistPageService {
    public artist$ = new BehaviorSubject<Artist>(null);
    public albums$ = new BehaviorSubject<PaginationResponse<Album>>(null);
    public artistQueueId$ = new BehaviorSubject<string>(null);
    public allSimilar$ = new BehaviorSubject<Artist[]>([]);
    public topSimilar$ = new BehaviorSubject<Artist[]>([]);
    public addingToLibrary$ = new BehaviorSubject<boolean>(false);
    public activeTab$ = new BehaviorSubject<string>(null);
    public fullDescription$ = new BehaviorSubject<string>(null);
    public shortDescription$ = new BehaviorSubject<string>(null);
    public thumbnailProfileImages$ = new BehaviorSubject<ProfileImage[]>([]);
    public fullSizeProfileImages$ = new BehaviorSubject<ProfileImage[]>([]);
    public tabs$ = new BehaviorSubject<{id: number, active: boolean}[]>([]);

    constructor(private settings: Settings) {}

    setArtist(artist: Artist, albums: PaginationResponse<Album>) {
        this.fullSizeProfileImages$.next([...artist.profile_images]);
        this.thumbnailProfileImages$.next(this.createThumbnails(artist.profile_images));
        this.artist$.next(artist);
        this.albums$.next(albums);
        this.artistQueueId$.next(queueId(this.artist$.value, 'allTracks'));
        this.allSimilar$.next(artist.similar);
        this.topSimilar$.next(artist.similar.slice(0, 4));
        let shortDescription: string = null;
        let fullDescription: string = null;
        if (artist.profile?.description) {
            shortDescription = artist.profile.description.slice(0, 300);
            if (shortDescription.length < artist.profile.description.length) {
                shortDescription += '...';
            }
            fullDescription = linkifyStr(artist.profile.description, {nl2br: true, attributes: {rel: 'nofollow'}})
        }
        this.shortDescription$.next(shortDescription);
        this.fullDescription$.next(fullDescription);
        this.tabs$.next(this.settings.getJson('artistPage.tabs', []).filter(t => t.active));
    }

    private createThumbnails(images: ProfileImage[]) {
        if (images?.length) {
            images = images.map(img => {
                const newImage = {...img};
                if (newImage.url.includes('upload.wikimedia.org')) {
                    const parts = newImage.url.split('/');
                    const filename = parts[parts.length - 1];
                    newImage.url = newImage.url.replace('/commons/', '/commons/thumb/') + '/300px-' + filename;
                }
                return newImage;
            });
        }
        return images;
    }
}
