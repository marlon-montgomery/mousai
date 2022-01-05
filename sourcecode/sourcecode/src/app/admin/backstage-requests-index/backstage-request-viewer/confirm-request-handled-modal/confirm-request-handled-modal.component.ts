import {ChangeDetectionStrategy, Component, Inject} from '@angular/core';
import {MAT_DIALOG_DATA, MatDialogRef} from '@angular/material/dialog';
import {FormControl, FormGroup} from '@angular/forms';
import {BackstageRequest} from '../../../../models/backstage-request';
import {BackstageRequestType} from '../../../../backstage/requests/backstage-request-type';

export interface ConfirmRequestApprovalModalData {
    request: BackstageRequest;
    type: 'approve'|'deny';
}

export interface ConfirmRequestApprovalResult {
    confirmed: boolean;
    verifyArtist?: boolean;
    notes?: string;
}

@Component({
    selector: 'confirm-request-approval-modal',
    templateUrl: './confirm-request-handled-modal.component.html',
    styleUrls: ['./confirm-request-handled-modal.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ConfirmRequestHandledModalComponent {
    public form = new FormGroup({
        verifyArtist: new FormControl(this.data.request.type === BackstageRequestType.verifyArtist),
        notes: new FormControl(),
    });

    constructor(
        private dialogRef: MatDialogRef<ConfirmRequestHandledModalComponent>,
        @Inject(MAT_DIALOG_DATA) public data: ConfirmRequestApprovalModalData,
    ) {}

    public confirm() {
        this.dialogRef.close({
            confirmed: true,
            ...this.form.value,
        } as ConfirmRequestApprovalResult);
    }

    public close() {
        this.dialogRef.close({
            confirmed: false,
        } as ConfirmRequestApprovalResult);
    }
}
