import {Component, Input, OnChanges, OnInit, SimpleChanges} from '@angular/core';
import {Track} from '../../../../models/Track';
import {DatatableService} from '@common/datatable/datatable.service';

@Component({
    selector: 'search-page-track-list',
    templateUrl: './search-page-track-list.component.html',
    styleUrls: ['./search-page-track-list.component.scss'],
    providers: [DatatableService],
})
export class SearchPageTrackListComponent implements OnInit, OnChanges {
    @Input() tracks: Track[];
    @Input() limit: number = null;

    constructor(public datatable: DatatableService<Track>) {}

    ngOnInit() {
        this.datatable.init();
    }

    ngOnChanges(changes: SimpleChanges) {
        let tracks = this.tracks || [];
        if (this.limit) {
            tracks = tracks.slice(0, 5);
        }
        this.datatable.data = tracks;
    }

}
