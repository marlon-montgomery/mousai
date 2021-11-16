import {Component, OnInit} from '@angular/core';
import {SettingsPanelComponent} from '@common/admin/settings/settings-panel.component';
import {finalize} from 'rxjs/operators';
import {BehaviorSubject} from 'rxjs';
import {FormControl} from '@angular/forms';
import {BackendErrorResponse} from '@common/core/types/backend-error-response';
import {scrollInvalidInputIntoView} from '@common/core/utils/scroll-invalid-input-into-view';

interface SearchableModel {
    model: string;
    name: string;
}

@Component({
    selector: 'search-settings',
    templateUrl: './search-settings.component.html',
    styleUrls: ['./search-settings.component.scss'],
    host: {class: 'settings-panel'},
})
export class SearchSettingsComponent extends SettingsPanelComponent implements OnInit {
    public models$ = new BehaviorSubject<SearchableModel[]>([]);
    public searchableModelControl = new FormControl(null);

    public importRecords() {
        this.loading$.next(true);
        this.http.post('admin/search/import', {
            model: this.searchableModelControl.value,
            driver: this.state.server.scout_driver
        }).pipe(finalize(() => this.loading$.next(false)))
        .subscribe(() => {
            this.toast.open('Records imported');
        }, (err: BackendErrorResponse) => {
            this.errors$.next({search_group: 'Could not import records: ' + err.message});
            scrollInvalidInputIntoView(this.errors$.value);
        });
    }

    ngOnInit() {
        this.http.get<{models: SearchableModel[]}>('admin/search/models').subscribe(response => {
            this.models$.next(response.models);
        });
    }
}
