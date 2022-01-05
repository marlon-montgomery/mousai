import {Component, OnInit} from '@angular/core';
import {ActivatedRoute} from '@angular/router';
import {Track} from '../../models/Track';
import {Artist} from '../../models/Artist';
import {Player} from '../player/player.service';
import {Translations} from '@common/core/translations/translations.service';
import {queueId} from '../player/queue-id';
import {DatatableService} from '@common/datatable/datatable.service';
import {Genre} from '../../models/Genre';

@Component({
    selector: 'radio-page',
    templateUrl: './radio-page.component.html',
    styleUrls: ['./radio-page.component.scss'],
    providers: [DatatableService],
})
export class RadioPageComponent implements OnInit {
    public tracks: Track[];
    public seed: Artist|Track|Genre;
    public type: string;
    public queueId: string;

    constructor(
        private route: ActivatedRoute,
        private player: Player,
        private i18n: Translations,
        public datatable: DatatableService<Track>,
    ) {}

    ngOnInit() {
        this.route.data.subscribe(data => {
            this.seed = data.radio.seed;
            this.type = this.i18n.t(data.radio.type);
            this.queueId = queueId(this.seed, 'radio');

            this.tracks = data.radio.recommendations.map(track => {
                return {...track};
            });

            this.datatable.init({
                initialData: this.tracks
            });
        });
    }
}
