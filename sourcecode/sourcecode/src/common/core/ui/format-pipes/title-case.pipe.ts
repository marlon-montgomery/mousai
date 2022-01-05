import {Pipe, PipeTransform} from '@angular/core';
import {TitleCasePipe as AngularTitleCasePipe} from '@angular/common';

@Pipe({
    name: 'titleCase',
    pure: true,
})
export class TitleCasePipe extends AngularTitleCasePipe implements PipeTransform {
    transform(value: string): string;
    transform(value: null|undefined): null;
    transform(value: string|null|undefined): string|null;
    transform(value: string | null | undefined): string | null {
        if ( ! value) {
            return '';
        }
        return super.transform(value.replace('-', ' ').replace('_', ' '));
    }
}
