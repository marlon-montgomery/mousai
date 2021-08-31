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
    switchMap,
} from 'rxjs/operators';
import {BehaviorSubject, Observable, of} from 'rxjs';
import {Users} from '../users.service';
import {User} from '../../core/types/models/User';
import {NormalizedModel} from '@common/core/types/models/normalized-model';

interface FindUserModalComponentData {
    normalizeValue?: boolean;
}

@Component({
    selector: 'find-user-modal',
    templateUrl: './find-user-modal.component.html',
    styleUrls: ['./find-user-modal.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class FindUserModalComponent implements OnInit {
    public searchFormControl = new FormControl();
    public loading$ = new BehaviorSubject(false);
    public users$ = new BehaviorSubject<User[]>([]);

    constructor(
        private dialogRef: MatDialogRef<FindUserModalComponent>,
        @Inject(MAT_DIALOG_DATA) public data: FindUserModalComponentData,
        private users: Users
    ) {}

    ngOnInit() {
        this.searchFormControl.valueChanges
            .pipe(
                debounceTime(250),
                distinctUntilChanged(),
                switchMap(query => this.searchUsers(query)),
                catchError(() => of([]))
            )
            .subscribe(users => {
                this.users$.next(users);
                this.loading$.next(false);
            });
    }

    private searchUsers(query: string): Observable<User[]> {
        this.loading$.next(true);
        if (!query) {
            return of([]);
        }
        return this.users.getAll({query});
    }

    public close(user?: User) {
        const value =
            user && this.data.normalizeValue ? this.normalizeUser(user) : user;
        this.dialogRef.close(value);
    }

    private normalizeUser(user?: User): NormalizedModel {
        if (user) {
            return {
                id: user.id,
                name: user.display_name,
                image: user.avatar,
                model_type: user.model_type,
            };
        }
        return null;
    }
}
