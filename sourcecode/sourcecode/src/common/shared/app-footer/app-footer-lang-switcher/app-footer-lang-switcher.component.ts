import {ChangeDetectionStrategy, Component} from '@angular/core';
import {Settings} from '@common/core/config/settings.service';
import {Translations} from '@common/core/translations/translations.service';
import {ThemeService} from '@common/core/theme.service';
import {ValueLists} from '@common/core/services/value-lists.service';
import {BehaviorSubject} from 'rxjs';
import {finalize} from 'rxjs/operators';
import {Localization} from '@common/core/types/models/Localization';
import {AppHttpClient} from '../../../core/http/app-http-client.service';
import {LocalizationWithLines} from '../../../core/types/localization-with-lines';

@Component({
    selector: 'app-footer-lang-switcher',
    templateUrl: './app-footer-lang-switcher.component.html',
    styleUrls: ['./app-footer-lang-switcher.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class AppFooterLangSwitcherComponent {
    localizations$ = new BehaviorSubject<Localization[]>([
        {id: 1, name: 'English', language: 'en'},
    ]);
    changingLang$ = new BehaviorSubject<boolean>(false);
    constructor(
        public settings: Settings,
        public i18n: Translations,
        public theme: ThemeService,
        private valueLists: ValueLists,
        private http: AppHttpClient
    ) {}

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
        this.http
            .post<{locale: LocalizationWithLines}>('users/me/locale', {
                locale: loc.language,
            })
            .pipe(finalize(() => this.changingLang$.next(false)))
            .subscribe(response => {
                this.i18n.setLocalization(response.locale);
            });
    }
}
