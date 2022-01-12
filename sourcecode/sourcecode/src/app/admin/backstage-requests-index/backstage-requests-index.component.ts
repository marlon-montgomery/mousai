import {ChangeDetectionStrategy, Component, OnInit} from '@angular/core';
import {DatatableService} from '@common/datatable/datatable.service';
import {CurrentUser} from '@common/auth/current-user';
import {Observable} from 'rxjs';
import {BackstageRequest} from '../../models/backstage-request';
import {BackstagRequestService} from '../../backstage/requests/backstag-request.service';
import {WebPlayerUrls} from '../../web-player/web-player-urls.service';
import {BACKSTAGE_REQUEST_INDEX_FILTERS} from './backstage-request-index-filters';

@Component({
    selector: 'backstage-requests-index',
    templateUrl: './backstage-requests-index.component.html',
    styleUrls: ['./backstage-requests-index.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush,
    providers: [DatatableService],
})
export class BackstageRequestsIndexComponent implements OnInit {
    requests$ = this.datatable.data$ as Observable<BackstageRequest[]>;
    filters = BACKSTAGE_REQUEST_INDEX_FILTERS;

    constructor(
        public datatable: DatatableService<BackstageRequest>,
        public currentUser: CurrentUser,
        private backstage: BackstagRequestService,
        public urls: WebPlayerUrls
    ) {}

    ngOnInit(): void {
        this.datatable.init({
            uri: 'backstage-request',
        });
    }

    public confirmRequestDeletion() {
        this.datatable.confirmResourceDeletion('requests').subscribe(() => {
            const ids = this.datatable.selectedRows$.value;
            this.backstage.deleteRequests(ids).subscribe(() => {
                this.datatable.reset();
            });
        });
    }
}
