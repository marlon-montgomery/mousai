import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
  name: 'labelFromFilename'
})
export class LabelFromFilenamePipe implements PipeTransform {

  transform(value: string): string {
    if (value) {
        return value.split('/').pop().split('.')[0].replace('-', ' ');
    }
  }
}
