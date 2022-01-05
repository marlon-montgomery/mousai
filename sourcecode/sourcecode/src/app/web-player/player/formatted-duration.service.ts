import {Injectable} from '@angular/core';
import {Translations} from '@common/core/translations/translations.service';

@Injectable({
    providedIn: 'root'
})
export class FormattedDuration {
    constructor(private i18n: Translations) {}

    public fromMilliseconds(ms: number) {
        return this.fromNumber(ms, 'ms');
    }

    public fromSeconds(seconds: number) {
        return this.fromNumber(seconds, 'sec');
    }

    private fromNumber(originalSeconds: number, type: 'sec'|'ms') {
        if ( ! originalSeconds || originalSeconds < 0) {
            return '0:00';
        }

        // create new date at "0:00:0" time
        const date = new Date(2000, 1, 1);
        if (type === 'sec') {
            date.setSeconds(originalSeconds);
        }  else {
            date.setMilliseconds(originalSeconds);
        }

        const minutes = (date.getMinutes() + (date.getHours() * 60)).toString(),
            seconds = this.prependZero(date.getSeconds().toString());

        return `${minutes}:${seconds}`;
    }

    private prependZero(number: string) {
        if (number.length === 1) {
            number = '0' + number;
        }

        return number;
    }

    public toVerboseString(ms: number): string {
        const date = new Date(ms);
        let str = '';

        const hours = date.getUTCHours();
        if (hours) str += hours + this.i18n.t('hr') + ' ';

        const minutes = date.getUTCMinutes();
        if (minutes) str += minutes + this.i18n.t('min') + ' ';

        const seconds = date.getUTCMinutes();
        if (seconds && !hours && !minutes) str += seconds + this.i18n.t('sec') + ' ';

        return str;
    }
}
