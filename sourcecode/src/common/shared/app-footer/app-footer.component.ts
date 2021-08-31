import {ChangeDetectionStrategy, Component} from '@angular/core';
import {Settings} from '@common/core/config/settings.service';
import {Translations} from '@common/core/translations/translations.service';
import {ThemeService} from '@common/core/theme.service';
import {ValueLists} from '@common/core/services/value-lists.service';
import {BehaviorSubject} from 'rxjs';
import {finalize} from 'rxjs/operators';
import {Localizations} from '@common/core/translations/localizations.service';
import {Localization} from '@common/core/types/models/Localization';

@Component({
    selector: 'app-footer',
    templateUrl: './app-footer.component.html',
    styleUrls: ['./app-footer.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class AppFooterComponent {
    copyrightText: string;
    localizations$ = new BehaviorSubject<Localization[]>([
        {id: 1, name: 'English', language: 'en'},
    ]);
    changingLang$ = new BehaviorSubject<boolean>(false);
    constructor(
        public settings: Settings,
        public i18n: Translations,
        public theme: ThemeService,
        private valueLists: ValueLists,
        private localizations: Localizations
    ) {
        const year = new Date().getFullYear();
        this.copyrightText = this.i18n.t(
            'Copyright © :year, All Rights Reserved',
            {year}
        );
    }

    langMenuOpened() {
        this.valueLists.get(['localizations']).subscribe(response => {
            this.localizations$.next(response.localizations);
        });
    }

    changeLanguage(loc: Localization) {
        if (this.i18n.isActive(loc)) {
            return;
        }
        this.changingLang$.next(true);
        this.localizations
            .get(loc.name)
            .pipe(finalize(() => this.changingLang$.next(false)))
            .subscribe(response => {
                this.i18n.setLocalization(response.localization);
            });
    }
}
