import {NgModule} from '@angular/core';
import {CommonModule} from '@angular/common';
import {AppFooterComponent} from './app-footer.component';
import {CustomMenuModule} from '@common/core/ui/custom-menu/custom-menu.module';
import {TranslationsModule} from '@common/core/translations/translations.module';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatMenuModule } from '@angular/material/menu';


@NgModule({
    declarations: [AppFooterComponent],
    imports: [
        CommonModule,
        CustomMenuModule,
        TranslationsModule,
        MatButtonModule,
        MatIconModule,
        MatMenuModule,
    ],
    exports: [AppFooterComponent],
})
export class AppFooterModule {}
