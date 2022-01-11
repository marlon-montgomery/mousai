import {ChangeDetectionStrategy, Component, OnInit} from '@angular/core';
import {FormBuilder} from '@angular/forms';
import {DatatableService} from '@common/datatable/datatable.service';
import {Model} from '@common/core/types/models/model';

@Component({
    selector: 'comment-index-filters',
    templateUrl: './comment-index-filters.component.html',
    styleUrls: ['./comment-index-filters.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush
})
export class CommentIndexFiltersComponent implements OnInit {
    public form = this.fb.group({
        deleted: null,
        created_at: null,
        user: null,
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
