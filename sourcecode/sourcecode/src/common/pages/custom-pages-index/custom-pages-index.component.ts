import {ChangeDetectionStrategy, Component, OnInit} from '@angular/core';
import {CustomPage} from '../../core/types/models/CustomPage';
import {Pages} from '../shared/pages.service';
import {CurrentUser} from '../../auth/current-user';
import {ActivatedRoute, Router} from '@angular/router';
import {Toast} from '../../core/ui/toast.service';
import {BackendErrorResponse} from '../../core/types/backend-error-response';
import {HttpErrors} from '../../core/http/errors/http-errors.enum';
import {DatatableService} from '../../datatable/datatable.service';
import {Settings} from '../../core/config/settings.service';
import {Observable} from 'rxjs';
import {CUSTOM_PAGE_FILTERS} from '@common/pages/custom-pages-index/cusomt-page-filters';

@Component({
    selector: 'custom-pages-index',
    templateUrl: './custom-pages-index.component.html',
    styleUrls: ['./custom-pages-index.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush,
    providers: [DatatableService],
})
export class CustomPagesIndexComponent implements OnInit {
    endpoint = this.route.snapshot.data.endpoint;
    filters = CUSTOM_PAGE_FILTERS;
    pages$ = this.datatable.data$ as Observable<CustomPage[]>;
    constructor(
        public settings: Settings,
        public datatable: DatatableService<CustomPage>,
        public currentUser: CurrentUser,
        protected customPages: Pages,
        protected toast: Toast,
        protected router: Router,
        protected route: ActivatedRoute
    ) {}

    ngOnInit() {
        this.datatable.init({
            uri: this.endpoint || Pages.BASE_URI,
            staticParams: {with: ['user']},
        });
    }

    maybeDeleteSelectedPages(pageIds?: number[]) {
        this.datatable
            .confirmResourceDeletion('tracking pixels')
            .subscribe(() => {
                this.customPages
                    .delete(pageIds || this.datatable.selectedRows$.value)
                    .subscribe(
                        () => {
                            this.datatable.reset();
                            this.toast.open('Pages deleted');
                        },
                        (errResponse: BackendErrorResponse) => {
                            this.toast.open(
                                errResponse.message || HttpErrors.Default
                            );
                        }
                    );
            });
    }

    showUserColumn(): boolean {
        return this.router.url.indexOf('admin') > -1;
    }

    getPageUrl(page: CustomPage): string {
        return (
            this.settings.getBaseUrl() + '/pages/' + page.id + '/' + page.slug
        );
    }

    viewName(name: string) {
        return name.replace(/_/g, ' ');
    }
}
