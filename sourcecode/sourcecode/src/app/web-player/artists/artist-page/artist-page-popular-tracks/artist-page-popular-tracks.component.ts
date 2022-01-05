import {Component, Input, OnChanges, OnInit, SimpleChanges} from '@angular/core';
import {Track} from '../../../../models/Track';
import {DatatableService} from '@common/datatable/datatable.service';

@Component({
    selector: 'artist-page-popular-tracks',
    templateUrl: './artist-page-popular-tracks.component.html',
    styleUrls: ['./artist-page-popular-tracks.component.scss'],
    providers: [DatatableService],
})
export class ArtistPagePopularTracksComponent implements OnInit, OnChanges {
    @Input() tracks: Track[];
    @Input() artistQueueId: string;
    public visibleTrackCount = 5;

    constructor(public datatable: DatatableService<Track>) {}

    ngOnInit() {
        this.datatable.init();
    }

    ngOnChanges(changes: SimpleChanges) {
        if (changes.tracks.currentValue !== changes.tracks.previousValue) {
            // reset count on artist change
            this.visibleTrackCount = 5;
            this.datatable.data = changes.tracks.currentValue.slice(0, this.visibleTrackCount);
        }
    }

    public togglePopularTracksCount() {
        this.visibleTrackCount = this.visibleTrackCount === 5 ? 20 : 5;
        this.datatable.data = this.tracks.slice(0, this.visibleTrackCount);
    }
}
