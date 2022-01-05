import {NgModule} from '@angular/core';
import {CommonModule} from '@angular/common';
import {GlobalSearchComponent} from '@common/admin/global-search/global-search.component';
import { TranslationsModule } from '@common/core/translations/translations.module';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';

@NgModule({
    declarations: [GlobalSearchComponent],
    imports: [CommonModule, TranslationsModule, FormsModule, ReactiveFormsModule],
    exports: [CommonModule, GlobalSearchComponent],
})
export class GlobalSearchModule {}
