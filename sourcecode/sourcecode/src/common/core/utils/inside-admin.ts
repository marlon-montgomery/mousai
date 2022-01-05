import {ActivatedRoute} from '@angular/router';

export function insideAdmin(route: ActivatedRoute): boolean {
    return !!route.snapshot.pathFromRoot.find(rc => rc.data.value?.adminRoot);
}
