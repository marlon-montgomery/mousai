import {ChangeDetectionStrategy, Component, OnInit} from '@angular/core';
import {MatDialogRef} from '@angular/material/dialog';
import {FormControl} from '@angular/forms';
import {catchError, debounceTime, distinctUntilChanged, switchMap} from 'rxjs/operators';
import {BehaviorSubject, Observable, of} from 'rxjs';
import {Users} from '../users.service';
import {User} from '../../core/types/models/User';
import {NormalizedModel} from '@common/core/types/models/normalized-model';

@Component({
    selector: 'find-user-modal',
    templateUrl: './find-user-modal.component.html',
    styleUrls: ['./find-user-modal.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class FindUserModalComponent implements OnInit {
    searchFormControl = new FormControl();
    loading$ = new BehaviorSubject(false);
    users$ = new BehaviorSubject<User[]>([]);

    constructor(
        private dialogRef: MatDialogRef<FindUserModalComponent>,
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

    close(user?: User) {
        this.dialogRef.close(this.normalizeUser(user));
    }

    private searchUsers(query: string): Observable<User[]> {
        this.loading$.next(true);
        if (!query) {
            return of([]);
        }
        return this.users.getAll({query});
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
