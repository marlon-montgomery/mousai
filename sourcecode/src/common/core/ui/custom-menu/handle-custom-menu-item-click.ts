import {MenuItem} from '@common/core/ui/custom-menu/menu-item';
import {isAbsoluteUrl} from '@common/core/utils/is-absolute-url';
import {getQueryParams} from '@common/core/utils/get-query-params';
import {Router} from '@angular/router';

export function handleCustomMenuItemClick(
    e: MouseEvent,
    menuItem: Partial<MenuItem>,
    router: Router
) {
    if (menuItem.type === 'scrollTo') {
        e.preventDefault();
        document
            .querySelector(menuItem.action)
            .scrollIntoView({
                block: 'center',
                inline: 'center',
                behavior: 'smooth',
            });
    } else if (!isAbsoluteUrl(menuItem.action)) {
        e.preventDefault();
        const parsed = parseRoute(menuItem.action);
        router.navigate([parsed.link], {queryParams: parsed.queryParams});
    }
}

function parseRoute(action: string) {
    const parts = action.split('?');
    return {link: parts[0], queryParams: getQueryParams(action)};
}
