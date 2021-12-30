import {
    ChangeDetectionStrategy,
    Component,
    ElementRef,
    Input,
    OnChanges,
    SimpleChanges,
    ViewEncapsulation
} from '@angular/core';
import {WebPlayerUrls} from '../../web-player-urls.service';
import {BehaviorSubject} from 'rxjs';
import {Artist, ARTIST_MODEL} from '../../../models/Artist';
import {User} from '@common/core/types/models/User';

@Component({
    selector: 'artists-links-list',
    templateUrl: './artists-links-list.component.html',
    styleUrls: ['./artists-links-list.component.scss'],
    encapsulation: ViewEncapsulation.None,
    changeDetection: ChangeDetectionStrategy.OnPush
})
export class ArtistsLinksListComponent implements OnChanges {
    @Input() artists: (Artist | User)[] = [];
    @Input() linksInNewTab = false;

    public artists$ = new BehaviorSubject<{ name: string, route: any[] | string }[]>([]);

    constructor(
        protected host: ElementRef,
        public urls: WebPlayerUrls
    ) {
    }

    ngOnChanges(changes: SimpleChanges) {
        if (changes.artists && changes.artists.currentValue) {
            this.normalizeArtists(changes.artists.currentValue);
        }
    }

    private normalizeArtists(artists: (Artist | User)[]) {
        const normalizedArtists = (artists || []).filter(a => !!a).map(artist => {
            if (artist.model_type === ARTIST_MODEL) {
                return {name: artist.name, route: this.urls.artist(artist)};
            } else {
                return {name: artist.display_name, route: this.urls.user(artist)};
            }
        });
        this.artists$.next(normalizedArtists);
    }
}
