import {ChangeDetectionStrategy, Component, OnInit} from '@angular/core';
import {
    ControlValueAccessor,
    FormArray,
    FormBuilder,
    NG_VALUE_ACCESSOR
} from '@angular/forms';
import {UserLink} from '../../../models/UserLink';
import {UploadQueueService} from '@common/uploads/upload-queue/upload-queue.service';

@Component({
    selector: 'profile-links-form-control',
    templateUrl: './profile-links-form-control.component.html',
    styleUrls: ['./profile-links-form-control.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush,
    providers: [UploadQueueService, {
        provide: NG_VALUE_ACCESSOR,
        useExisting: ProfileLinksFormControlComponent,
        multi: true,
    }]
})
export class ProfileLinksFormControlComponent implements ControlValueAccessor, OnInit {
    public form = new FormArray([]);
    private propagateChange: (links: object[]) => void;

    constructor(private fb: FormBuilder) {}

    ngOnInit(): void {
        this.form.valueChanges.subscribe((value: object[]) => {
            if (this.propagateChange) {
                this.propagateChange(value);
            }
        });
    }

    public addNewLink(link: Partial<UserLink> = {}) {
        this.form.push(this.fb.group({url: [link.url || ''], title: [link.title || '']}));
    }

    public deleteLink(index: number) {
        this.form.removeAt(index);
    }

    public writeValue(value: object[]) {
        (value || []).forEach(link => {
            this.addNewLink(link);
        });
    }

    public registerOnChange(fn: (links: object[]) => void) {
        this.propagateChange = fn;
    }

    public registerOnTouched() {}
}
