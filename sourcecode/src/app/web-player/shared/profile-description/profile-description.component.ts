import {ChangeDetectionStrategy, Component, Input} from '@angular/core';

@Component({
    selector: 'profile-description',
    templateUrl: './profile-description.component.html',
    styleUrls: ['./profile-description.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush
})
export class ProfileDescriptionComponent {
    @Input() description: string;
    @Input() country: string;
    @Input() city: string;
    @Input() onDarkBg: boolean;
}
