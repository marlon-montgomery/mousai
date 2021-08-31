import {Component, OnInit} from '@angular/core';
import {ActivatedRoute} from '@angular/router';
import {Channel} from '../../../admin/channels/channel';
import {ReplaySubject} from 'rxjs';

@Component({
    selector: 'channel-fallback-host',
    templateUrl: './channel-fallback-host.component.html',
    styleUrls: ['./channel-fallback-host.component.scss'],
})
export class ChannelFallbackHostComponent implements OnInit {
    public channel$ = new ReplaySubject<Channel>(1);

    constructor(private route: ActivatedRoute) {}

    ngOnInit() {
        this.route.data.subscribe(data => {
            this.channel$.next((data.api && data.api.channel) || null);
        });
    }
}
