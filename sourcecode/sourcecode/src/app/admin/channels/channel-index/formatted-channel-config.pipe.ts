import {Pipe, PipeTransform} from '@angular/core';
import {startCase} from '@common/core/utils/start-case';

@Pipe({
    name: 'formattedChannelConfig',
    pure: true,
})
export class FormattedChannelConfigPipe implements PipeTransform {
    transform(value: string): string {
        return value ? startCase(value) : value;
    }
}
