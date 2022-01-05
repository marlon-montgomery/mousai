import {NgModule} from '@angular/core';
import {CommonModule} from '@angular/common';
import {SearchInputWithFiltersComponent} from './search-input-with-filters.component';
import {ActiveFiltersComponent} from './active-filters/active-filters.component';
import {ActiveFilterComponent} from './active-filters/active-filter/active-filter.component';
import {OperatorSelectComponent} from './active-filters/operator-select/operator-select.component';
import {FilterSuggestionsComponent} from './filter-suggestions/filter-suggestions.component';
import {OperatorDisplayNamePipe} from './active-filters/operator-select/operator-display-name.pipe';
import {SelectModelControlComponent} from './active-filters/select-model-control/select-model-control.component';
import {TranslationsModule} from '@common/core/translations/translations.module';
import {ReactiveFormsModule} from '@angular/forms';
import {MatIconModule} from '@angular/material/icon';
import {MatButtonModule} from '@angular/material/button';
import {SelectModelDialogComponent} from '@common/datatable/datatable-filters/select-model-dialog/select-model-dialog.component';
import {MatDialogModule} from '@angular/material/dialog';
import {LoadingIndicatorModule} from '@common/core/ui/loading-indicator/loading-indicator.module';

@NgModule({
    declarations: [
        SearchInputWithFiltersComponent,
        ActiveFiltersComponent,
        ActiveFilterComponent,
        OperatorSelectComponent,
        FilterSuggestionsComponent,
        OperatorDisplayNamePipe,
        SelectModelControlComponent,
        SelectModelDialogComponent,
    ],
    imports: [
        CommonModule,
        TranslationsModule,
        ReactiveFormsModule,
        MatIconModule,
        MatButtonModule,
        MatDialogModule,
        LoadingIndicatorModule,
    ],
    exports: [SearchInputWithFiltersComponent],
})
export class SearchInputWithFiltersModule {}
