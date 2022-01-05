import {Pipe, PipeTransform} from '@angular/core';
import {FilterOperator} from '../../filter-config/datatable-filter';
import {FILTER_OPERATOR_NAMES} from '../../../filter-operator-names';
import {BreakpointsService} from '../../../../../core/ui/breakpoints.service';

@Pipe({
    name: 'operatorDisplayName',
})
export class OperatorDisplayNamePipe implements PipeTransform {
    constructor(private breakpoints: BreakpointsService) {}
    transform(value: FilterOperator, compact: boolean): string {
        return compact || this.breakpoints.isMobile$.value
            ? value
            : FILTER_OPERATOR_NAMES[value];
    }
}
