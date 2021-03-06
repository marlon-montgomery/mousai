import {ChangeDetectionStrategy, Component, Inject, OnInit} from '@angular/core';
import {MAT_DIALOG_DATA, MatDialogRef} from '@angular/material/dialog';
import {Permission} from '@common/core/types/models/permission';
import {ValueLists} from '@common/core/services/value-lists.service';
import {SelectionModel} from '@common/core/utils/SelectionModel';
import {groupBy} from '@common/core/utils/group-by';
import {flattenArray} from '@common/core/utils/flatten-array';
import {BehaviorSubject} from 'rxjs';
import {filter, finalize} from 'rxjs/operators';
import {FormControl} from '@angular/forms';

export interface SelectPermissionsModalData {
    initialPermissions: Permission[];
    valueListKey: string;
    hideRestrictions: boolean;
}

@Component({
    selector: 'select-permissions-modal',
    templateUrl: './select-permissions-modal.component.html',
    styleUrls: ['./select-permissions-modal.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class SelectPermissionsModalComponent implements OnInit {
    public allPermissions$ = new BehaviorSubject<{[key: string]: Permission[]}>({});
    public filteredPermissions$ = new BehaviorSubject<{[key: string]: Permission[]}>({});
    public loading$ = new BehaviorSubject(false);
    public selectionModel = new SelectionModel(this.data.initialPermissions || [], 'name');
    public showAdvancedControl = new FormControl(false);

    constructor(
        private dialogRef: MatDialogRef<SelectPermissionsModalComponent>,
        @Inject(MAT_DIALOG_DATA) public data: SelectPermissionsModalData,
        private values: ValueLists,
    ) {}

    ngOnInit() {
        this.fetchAllPermissions();
        this.showAdvancedControl.valueChanges.subscribe(() => {
            console.log('x');
            this.setShowAdvanced();
        });
    }

    public confirm() {
        this.close(this.getSelectedPermissions());
    }

    public close(permissions?: Permission[]) {
        this.dialogRef.close(permissions);
    }

    private fetchAllPermissions() {
        const valueListKey = this.data.valueListKey || 'permissions';
        this.loading$.next(true);
        this.values.get([valueListKey])
            .pipe(finalize(() => this.loading$.next(false)))
            .subscribe(response => {
                const allPermissions = groupBy<Permission>(this.mergeRestrictions(response[valueListKey]), 'group');
                this.allPermissions$.next(allPermissions);
                this.setShowAdvanced();
            });
    }

    public setShowAdvanced() {
        const allPermissions = this.allPermissions$.value;
        const filteredPermissions = {};
        Object.keys(allPermissions).forEach(groupName => {
            const groupPermissions = allPermissions[groupName].filter(permission => {
                return this.showAdvancedControl.value || !permission.advanced;
            });
            if (groupPermissions.length) {
                filteredPermissions[groupName] = groupPermissions;
            }
        });
        this.filteredPermissions$.next(filteredPermissions);
    }

    private getSelectedPermissions(): Permission[] {
        const allPermissions = flattenArray(Object.values(this.allPermissions$.value));
        return allPermissions.filter(permission => {
            return this.selectionModel.isSelected(permission);
        });
    }

    private mergeRestrictions(allPermissions: Permission[]) {
        return allPermissions.map(permission => {
            // merge restriction definition from "allPermissions" and value set for that
            // restriction on pivot table so can bind ngModel to "allRestrictions" variable
            const modelPermission = this.selectionModel.getValue(permission.name);
            permission.restrictions = (permission.restrictions || []).map(restriction => {
                let modelRestriction = {};
                if (modelPermission && modelPermission.restrictions) {
                    modelRestriction = modelPermission.restrictions.find(r => r.name === restriction.name) || {};
                }
                return {...restriction, ...modelRestriction};
            });
            return permission;
        });
    }

    public viewName(name: string) {
        return name.replace(/_/g, ' ');
    }
}
