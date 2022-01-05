import {
    AfterViewInit,
    Component,
    ElementRef, HostBinding, Input,
    OnDestroy,
    ViewEncapsulation
} from '@angular/core';

@Component({
    selector: 'app-marquee',
    template: '<ng-content></ng-content>',
    styleUrls: ['./marquee.component.scss'],
    encapsulation: ViewEncapsulation.None,
})
export class MarqueeComponent implements AfterViewInit, OnDestroy {
    @Input()
    @HostBinding('style.animation-duration.s')
    animationDuration = 10;

    @Input()
    pausable = false;

    protected interval: number = undefined;

    protected width: number = undefined;
    protected scrollWidth: number = undefined;

    constructor(protected host: ElementRef) {
    }

    set MaxTranslate(value: number) {
        value = value < 0 ? 0 : value;

        this.host.nativeElement.style.setProperty('--max-translate', `${value}px`);
        this.host.nativeElement.style.setProperty('animation-name', value > 0 ? 'marquee' : 'none');
    }

    ngAfterViewInit() {
        this.interval = setInterval(() => this.calculate(), 300);
    }

    ngOnDestroy() {
        clearInterval(this.interval);
    }

    calculate() {
        const host = this.host.nativeElement;

        const computedHostStyles = getComputedStyle(host);
        const hostWidth = parseInt(computedHostStyles.width, 10);
        const hostScrollWidth = host.scrollWidth;

        if (this.width !== hostWidth || this.scrollWidth !== hostScrollWidth) {
            this.width = hostWidth;
            this.scrollWidth = hostScrollWidth;
            this.MaxTranslate = this.scrollWidth - this.width;
        }
    }
}
