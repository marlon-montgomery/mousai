import {EventEmitter, Injectable} from '@angular/core';
import {Settings} from '../config/settings.service';
import {LocalizationWithLines} from '../types/localization-with-lines';
import {Localization} from '@common/core/types/models/Localization';

@Injectable({
    providedIn: 'root',
})
export class Translations {
    localizationChange = new EventEmitter();

    public localization: LocalizationWithLines = {
        model: {name: 'English', id: 1, language: 'en'},
        name: '',
        lines: {},
    };

    constructor(private settings: Settings) {}

    public t(transKey: string, values?: object): string {
        if (!transKey) return '';
        if (!this.translationsEnabled()) {
            return this.replacePlaceholders(transKey, values);
        }
        const translation =
            this.localization.lines[transKey.toLowerCase().trim()] || transKey;
        return this.replacePlaceholders(translation, values);
    }

    private replacePlaceholders(message: string, values: object): string {
        if (!values) return message;

        const keys = Object.keys(values);

        keys.forEach(key => {
            const regex = new RegExp(':' + key, 'g');
            message = message.replace(regex, values[key]);
        });

        return message;
    }

    isActive(loc: Localization): boolean {
        return loc.id === this.localization.model.id;
    }

    /**
     * Set active localization.
     */
    public setLocalization(localization: LocalizationWithLines) {
        if (!localization || !localization.lines || !localization.model) return;
        if (this.localization.model.name === localization.model.name) return;

        localization.lines = this.objectKeysToLowerCase(localization.lines);
        this.localization = localization;

        this.localizationChange.emit();
    }

    private objectKeysToLowerCase(object: object) {
        const newObject = {};

        Object.keys(object).forEach(key => {
            newObject[key.toLowerCase()] = object[key];
        });

        return newObject;
    }

    translationsEnabled(): boolean {
        return (
            this.settings.get('i18n.enable') &&
            // if selected language is english and no lines
            // were changed, then there's no need to translate
            (this.localization.model.language !== 'en' ||
                this.localization.model.created_at !==
                    this.localization.model.updated_at)
        );
    }
}
