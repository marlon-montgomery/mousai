import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {ControlValueAccessor, FormControl, NG_VALUE_ACCESSOR} from '@angular/forms';
import {BehaviorSubject} from 'rxjs';
import {Settings} from '@common/core/config/settings.service';

@Component({
    selector: 'slug-control',
    templateUrl: './slug-control.component.html',
    styleUrls: ['./slug-control.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush,
    providers: [{
        provide: NG_VALUE_ACCESSOR,
        useExisting: SlugControlComponent,
        multi: true,
    }]
})
export class SlugControlComponent implements ControlValueAccessor {
    // tslint:disable-next-line:ban-types
    private propagateChange: Function;
    public slug$ = new BehaviorSubject<string>('');
    public editing$ = new BehaviorSubject<boolean>(false);
    public slugControl = new FormControl();
    @Input() prefix: string;

    @Input() set baseUri(uri: string) {
        uri = uri || this.defaultBaseUri;
        this.fullBaseUri = uri.endsWith('/') ? uri : uri + '/';
    }

    private defaultBaseUri: string = this.settings.getBaseUrl() + '/';
    fullBaseUri = this.defaultBaseUri;

    constructor(public settings: Settings) {
    }

    public writeValue(value: string) {
        this.slug$.next(value);
        this.slugControl.setValue(value);
    }

    // tslint:disable-next-line:ban-types
    public registerOnChange(fn: Function) {
        this.propagateChange = fn;
    }

    public registerOnTouched() {
    }

    public save() {
        this.slug$.next(this.slugControl.value);
        this.propagateChange(this.slug$.value);
        this.editing$.next(false);
    }

    public startEditing() {
        this.editing$.next(true);
    }

    public getPrefix() {
        return this.prefix ? this.prefix + '/' : '';
    }
}
