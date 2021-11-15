import { ConnectedPosition } from '@angular/cdk/overlay';

export const BOTTOM_POSITION: ConnectedPosition[] = [
    {originX: 'center', originY: 'bottom', overlayX: 'center', overlayY: 'top'},
    {originX: 'center', originY: 'top', overlayX: 'center', overlayY: 'bottom'},
];
