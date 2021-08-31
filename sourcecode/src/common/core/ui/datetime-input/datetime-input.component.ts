import { ChangeDetectionStrategy, ChangeDetectorRef, Component, Input } from '@angular/core';
import {
    ControlValueAccessor,
    FormBuilder,
    NG_VALUE_ACCESSOR,
} from '@angular/forms';
import { val } from 'cheerio/lib/api/attributes';

@Component({
    selector: 'datetime-input',
    templateUrl: './datetime-input.component.html',
    styleUrls: ['./datetime-input.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush,
    providers: [
        {
            provide: NG_VALUE_ACCESSOR,
            useExisting: DatetimeInputComponent,
            multi: true,
        },
    ],
})
export class DatetimeInputComponent implements ControlValueAccessor {
    @Input() id: string;
    @Input() currentDateAsDefault = false;

    currentDate: string;
    currentTime: string;
    private initiated = false;

    private propagateChange: Function;
    form = this.fb.group({
        date: [''],
        time: [''],
    });

    constructor(private fb: FormBuilder, private cd: ChangeDetectorRef) {
        this.setCurrentDatetime();
    }

    writeValue(value: string) {
        value = (value || '').replace('Z', '');
        let [date, time] = value.includes('T') ? value.split('T') : value.split(' ');
        time = time ? time.substr(0, 5) : '';

        if (!this.initiated && this.currentDateAsDefault) {
            date = date || this.currentDate;
            time = time || this.currentTime;
        }

        this.initiated = true;
        this.form.patchValue({date, time});
    }

    registerOnChange(fn: Function) {
        this.propagateChange = fn;
        this.form.valueChanges.subscribe(value => {
            let datetime: string;
            if ( ! value.date) {
                datetime = null;
            } else {
                if (!value.time) {
                    value.time = '00:00';
                }
                datetime = `${value.date} ${value.time}`;
                // add seconds, if don't already exist
                if (datetime.split(':').length === 2) {
                    datetime += ':00';
                }
            }
            this.propagateChange(datetime);
        });
    }

    registerOnTouched() {}

    setDisabledState(isDisabled: boolean) {
        if (isDisabled) {
            this.form.disable();
        } else {
            this.form.enable();
        }
        this.cd.markForCheck();
    }

    clearValue() {
        this.form.patchValue({
            date: null,
            time: null,
        });
    }

    private setCurrentDatetime() {
        const [date, time] = new Date()
            .toISOString()
            .replace('Z', '')
            .split('T');
        const [hours, minutes] = time.split(':');
        this.currentDate = date;
        this.currentTime = `${hours}:${minutes}`;
    }
}
