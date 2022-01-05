import {ChangeDetectionStrategy, Component, Input} from '@angular/core';

@Component({
  selector: 'profile-follower-info',
  templateUrl: './profile-follower-info.component.html',
  styleUrls: ['./profile-follower-info.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class ProfileFollowerInfoComponent {
    @Input() followers: number;
    @Input() followed: number;
}
