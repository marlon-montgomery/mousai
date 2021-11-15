import {ChangeDetectionStrategy, Component} from '@angular/core';
import {ThemeService} from '@common/core/theme.service';

@Component({
  selector: 'backstage-host',
  templateUrl: './backstage-host.component.html',
  styleUrls: ['./backstage-host.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class BackstageHostComponent {
    constructor(public themes: ThemeService) {}
}
