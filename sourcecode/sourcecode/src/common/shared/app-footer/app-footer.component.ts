import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {Translations} from '@common/core/translations/translations.service';

@Component({
    selector: 'app-footer',
    templateUrl: './app-footer.component.html',
    styleUrls: ['./app-footer.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class AppFooterComponent {
    copyrightText: string;
    constructor(private i18n: Translations) {
        const year = new Date().getFullYear();
        this.copyrightText = this.i18n.t('Copyright © :year, All Rights Reserved', {
            year,
        });
    }
}
