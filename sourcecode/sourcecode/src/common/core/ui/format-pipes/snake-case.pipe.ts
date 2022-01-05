import {Pipe, PipeTransform} from '@angular/core';
import { snakeCase } from '@common/core/utils/snake-case';

@Pipe({
    name: 'snakeCase',
    pure: true,
})
export class SnakeCasePipe implements PipeTransform {
    transform(value: string | null | undefined): string | null {
        if (!value) {
            return '';
        }
        return snakeCase(value);
    }
}
