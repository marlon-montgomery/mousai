import {Component, OnInit, ViewEncapsulation} from '@angular/core';
import {Genres} from '../../web-player/genres/genres.service';
import {Genre} from '../../models/Genre';
import {CrupdateGenreModalComponent} from './crupdate-genre-modal/crupdate-genre-modal.component';
import {CurrentUser} from '@common/auth/current-user';
import {WebPlayerUrls} from '../../web-player/web-player-urls.service';
import {DatatableService} from '@common/datatable/datatable.service';
import {Observable} from 'rxjs';

interface IndexGenre extends Genre {
    artists_count?: number;
    updated_at: string;
}

@Component({
    selector: 'genres',
    templateUrl: './genres.component.html',
    styleUrls: ['./genres.component.scss'],
    encapsulation: ViewEncapsulation.None,
    providers: [DatatableService],
})
export class GenresComponent implements OnInit {
    public genres$ = this.datatable.data$ as Observable<IndexGenre[]>;
    constructor(
        public datatable: DatatableService<IndexGenre>,
        private genres: Genres,
        public currentUser: CurrentUser,
        public urls: WebPlayerUrls,
    ) {}

    ngOnInit() {
        this.datatable.init({
            uri: 'genres',
            staticParams: {withCount: 'artists'}
        });
    }

    public openCrupdateGenreModal(genre?: IndexGenre) {
        this.datatable.openCrupdateResourceModal(CrupdateGenreModalComponent, {genre})
            .subscribe(() => {
                this.datatable.reset();
            });
    }

    public confirmGenresDeletion() {
        this.datatable.confirmResourceDeletion('genres').subscribe(() => {
            this.genres.delete(this.datatable.selectedRows$.value).subscribe(() => {
                this.datatable.reset();
            });
        });
    }
}
