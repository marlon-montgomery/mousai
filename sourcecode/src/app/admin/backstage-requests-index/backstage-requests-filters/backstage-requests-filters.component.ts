import {ChangeDetectionStrategy, Component, OnInit} from '@angular/core';
import {FormBuilder} from '@angular/forms';
import {DatatableService} from '@common/datatable/datatable.service';
import {Model} from '@common/core/types/models/model';

@Component({
    selector: 'backstage-requests-filters',
    templateUrl: './backstage-requests-filters.component.html',
    styleUrls: ['./backstage-requests-filters.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush
})
export class BackstageRequestsFiltersComponent implements OnInit {
    public form = this.fb.group({
        type: null,
        status: null,
        created_at: null,
        requester: null,
    });

    constructor(
        private fb: FormBuilder,
        private datable: DatatableService<Model>,
    ) {
        this.form.patchValue(this.datable.filters$.value);
    }

    ngOnInit() {
        this.form.valueChanges.subscribe(value => {
            this.datable.filters$.next(value);
        });
    }
}
