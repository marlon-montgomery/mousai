import {
    ChangeDetectionStrategy,
    ChangeDetectorRef,
    Component,
    OnInit
} from '@angular/core';
import {Settings} from '@common/core/config/settings.service';
import {Artists} from '../../../web-player/artists/artists.service';
import {ActivatedRoute, Router} from '@angular/router';
import {Artist} from '../../../models/Artist';
import {Toast} from '@common/core/ui/toast.service';
import {UploadQueueService} from '@common/uploads/upload-queue/upload-queue.service';
import {FormArray, FormBuilder, FormControl} from '@angular/forms';
import {GENRE_MODEL} from '../../../models/Genre';
import {map} from 'rxjs/operators';
import {Search} from '../../../web-player/search/search.service';
import {BackendErrorResponse} from '@common/core/types/backend-error-response';
import {insideAdmin} from '@common/core/utils/inside-admin';
import {PaginationResponse} from '@common/core/types/pagination/pagination-response';
import {Album} from '../../../models/Album';
import {ComponentCanDeactivate} from '@common/guards/pending-changes/component-can-deactivate';
import {WebPlayerUrls} from '../../../web-player/web-player-urls.service';

@Component({
    selector: 'crupdate-artist-page',
    templateUrl: './crupdate-artist-page.component.html',
    styleUrls: ['./crupdate-artist-page.component.scss'],
    providers: [UploadQueueService],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class CrupdateArtistPageComponent implements OnInit, ComponentCanDeactivate {
    public insideAdmin = false;
    public errors: {[K in keyof Partial<Artist>]: string} = {};
    public artist: Artist;
    public albums: Album[] = [];

    public form = this.fb.group({
        name: [''],
        bitclout: [''],
        verified: [''],
        image_small: [''],
        genres: [[]],
        description: [''],
        country: [''],
        city: [''],
        profile_images: this.fb.array([]),
        links: [[]],
    });

    constructor(
        private settings: Settings,
        private uploadQueue: UploadQueueService,
        private artists: Artists,
        private route: ActivatedRoute,
        private toast: Toast,
        private router: Router,
        private fb: FormBuilder,
        private cd: ChangeDetectorRef,
        private search: Search,
        private urls: WebPlayerUrls,
    ) {}

    ngOnInit() {
        this.bindToRouteData();
        this.insideAdmin = this.router.url.includes('admin');
    }

    public createOrUpdate() {
        this.artist ? this.update() : this.create();
    }

    public create() {
        return this.artists.create(this.form.value).subscribe(response => {
            this.form.markAsPristine();
            this.artist = response.artist;
            this.toast.open('Artist created.');
            this.router.navigate(this.urls.editArtist(this.artist.id, true), {replaceUrl: true});
            this.errors = {};
        }, (errResponse: BackendErrorResponse) => {
            this.errors = errResponse.errors;
            this.cd.detectChanges();
        });
    }

    public update() {
        return this.artists.update(this.artist.id, this.form.value).subscribe(() => {
            this.form.markAsPristine();
            this.toast.open('Artist updated.');
            this.router.navigate(['/admin/artists']);
        }, (errResponse: BackendErrorResponse) => {
            this.errors = errResponse.errors;
            this.cd.detectChanges();
        });
    }

    public profileImagesArray() {
        return this.form.get('profile_images') as FormArray;
    }

    private bindToRouteData() {
        this.route.data.subscribe((data: {api: {artist?: Artist, albums?: Album[]}}) => {
            if (data.api) {
                this.artist = data.api.artist;
                this.albums = data.api.albums;
                this.form.patchValue({
                    name: data.api.artist.name,
                    bitclout: data.api.artist.bitclout,
                    verified: data.api.artist.verified,
                    image_small: data.api.artist.image_small,
                    genres: (data.api.artist.genres || []).map(g => g.name),
                    description: data.api.artist.profile?.description,
                    country: data.api.artist.profile?.country,
                    city: data.api.artist.profile?.city,
                    profile_images: [],
                    links: data.api.artist.links,
                });
                (data.api.artist.profile_images || []).forEach(img => {
                    this.addProfileImage(img.url);
                });
            }
        });
        this.addProfileImage();
    }

    public addProfileImage(url = '') {
        this.profileImagesArray().push(new FormControl(url));
    }

    public removeProfileImage(index: number) {
        this.profileImagesArray().removeAt(index);
    }

    public suggestGenreFn = (query: string) => {
        return this.search.media(query, {types: [GENRE_MODEL], limit: 5})
            .pipe(map(response => response.results.genres.map(genre => genre.name)));
    }

    public canDeactivate(): boolean {
        return !this.form.dirty;
    }
}
