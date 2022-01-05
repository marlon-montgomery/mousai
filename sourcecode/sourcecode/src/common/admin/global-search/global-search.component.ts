import {Component, OnInit, ChangeDetectionStrategy} from '@angular/core';
import {FormControl} from '@angular/forms';
import {debounceTime, switchMap} from 'rxjs/operators';
import {AppHttpClient} from '@common/core/http/app-http-client.service';
import {BackendResponse} from '@common/core/types/backend-response';
import {BehaviorSubject} from 'rxjs';

@Component({
    selector: 'global-search',
    templateUrl: './global-search.component.html',
    styleUrls: ['./global-search.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class GlobalSearchComponent implements OnInit {
    searchControl = new FormControl();
    results$ = new BehaviorSubject<{[key: string]: any[]}>({});

    constructor(private http: AppHttpClient) {}

    ngOnInit(): void {
        this.searchControl.valueChanges
            .pipe(
                debounceTime(300),
                switchMap(query => this.search(query))
            )
            .subscribe(response => {
                this.results$.next(response.results);
            });
    }

    private search(query: string): BackendResponse<any> {
        return this.http.get('search/global', {query});
    }
}
