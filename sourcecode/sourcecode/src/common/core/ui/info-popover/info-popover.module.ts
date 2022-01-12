import {NgModule} from '@angular/core';
import {CommonModule} from '@angular/common';
import {InfoPopoverComponent} from '@common/core/ui/info-popover/info-popover.component';
import {MatIconModule} from '@angular/material/icon';
import {MatButtonModule} from '@angular/material/button';

@NgModule({
    declarations: [InfoPopoverComponent],
    imports: [
        CommonModule,

        // material
        MatIconModule,
        MatButtonModule,
    ],
    exports: [InfoPopoverComponent],
})
export class InfoPopoverModule {}
