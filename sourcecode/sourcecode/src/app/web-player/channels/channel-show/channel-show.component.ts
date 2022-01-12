import {Component, Input, OnInit} from '@angular/core';
import {ActivatedRoute} from '@angular/router';
import {BehaviorSubject} from 'rxjs';
import {Channel} from '../../../admin/channels/channel';
import {WebPlayerState} from '../../web-player-state.service';

@Component({
    selector: 'channel-show',
    templateUrl: './channel-show.component.html',
    styleUrls: ['./channel-show.component.scss'],
})
export class ChannelShowComponent implements OnInit {
    @Input() set channel(channel: Channel) {
        this.channel$.next(channel);
    }

    public channel$ = new BehaviorSubject<Channel>(null);

    constructor(private route: ActivatedRoute, private state: WebPlayerState) {}

    ngOnInit() {
        if (this.state.scrollContainer) {
            this.state.scrollContainer.nativeElement.scrollTop = 0;
        }
        this.route.data.subscribe(data => {
            if (data.api && data.api.channel) {
                this.channel$.next(data.api.channel);
            }
        });
    }
}
