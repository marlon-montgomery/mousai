import {Pipe, PipeTransform} from '@angular/core';
import {isAbsoluteUrl} from '@common/core/utils/is-absolute-url';
import {Settings} from '@common/core/config/settings.service';

@Pipe({
    name: 'backgroundUrl',
})
export class BackgroundUrlPipe implements PipeTransform {
    constructor(private settings: Settings) {}

    transform(url: string): string {
        if (!url || url === 'none') {
            return null;
        }
        if (url.includes('linear-gradient')) {
            return url;
        }
        if (!isAbsoluteUrl(url)) {
            if (url.startsWith('storage')) {
                url = `${this.settings.getBaseUrl()}/${url}`;
            } else {
                url = this.settings.getAssetUrl(`images/${url}`);
            }
        }
        return `url(${url})`;
    }
}
