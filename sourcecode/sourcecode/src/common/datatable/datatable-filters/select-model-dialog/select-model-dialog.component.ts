import {
    ChangeDetectionStrategy,
    Component,
    Inject,
    OnInit,
} from '@angular/core';
import {MAT_DIALOG_DATA, MatDialogRef} from '@angular/material/dialog';
import {FormControl} from '@angular/forms';
import {
    catchError,
    debounceTime,
    distinctUntilChanged,
    map,
    switchMap,
} from 'rxjs/operators';
import {BehaviorSubject, Observable, of} from 'rxjs';
import {NormalizedModel} from '@common/core/types/models/normalized-model';
import {AppHttpClient} from '@common/core/http/app-http-client.service';

interface SelectModelDialogData {
    modelType: string;
}

@Component({
    selector: 'select-model-dialog',
    templateUrl: './select-model-dialog.component.html',
    styleUrls: ['./select-model-dialog.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class SelectModelDialogComponent implements OnInit {
    modelName: string;
    searchFormControl = new FormControl();
    loading$ = new BehaviorSubject(false);
    results$ = new BehaviorSubject<NormalizedModel[]>([]);

    constructor(
        private dialogRef: MatDialogRef<SelectModelDialogComponent>,
        private http: AppHttpClient,
        @Inject(MAT_DIALOG_DATA) public data: SelectModelDialogData
    ) {
        this.modelName = this.data.modelType;
    }

    ngOnInit() {
        this.searchFormControl.valueChanges
            .pipe(
                debounceTime(250),
                distinctUntilChanged(),
                switchMap(query => this.searchModel(query)),
                catchError(() => of([]))
            )
            .subscribe(users => {
                this.results$.next(users);
                this.loading$.next(false);
            });
    }

    private searchModel(query: string): Observable<NormalizedModel[]> {
        this.loading$.next(true);
        if (!query) {
            return of([]);
        }
        return this.http
            .get<{results: NormalizedModel[]}>('search/global/model', {
                modelType: this.data.modelType,
                query,
            })
            .pipe(map(response => response.results));
    }

    close(selectedModel?: NormalizedModel) {
        this.dialogRef.close(selectedModel);
    }
}
