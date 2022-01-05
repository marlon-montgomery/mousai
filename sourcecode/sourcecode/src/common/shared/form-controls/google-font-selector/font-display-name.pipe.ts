import {Pipe, PipeTransform} from '@angular/core';

@Pipe({
    name: 'fontDisplayName',
})
export class FontDisplayNamePipe implements PipeTransform {
    transform(fontFamily: string): string {
        if (!fontFamily) {
            return null;
        }
        return fontFamily.split(',')[0].replace(/"/g, '').trim();
    }
}
