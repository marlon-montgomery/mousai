import {Component, Input} from '@angular/core';
import {WebPlayerUrls} from '../../web-player-urls.service';
import {Genre} from '../../../models/Genre';

@Component({
    selector: 'genre-item',
    templateUrl: './genre-item.component.html',
    styleUrls: ['./genre-item.component.scss'],
    host: {'class': 'media-grid-item'},
})
export class GenreItemComponent {
    @Input() genre: Genre;

    constructor(
        public urls: WebPlayerUrls,
    ) {}
}
