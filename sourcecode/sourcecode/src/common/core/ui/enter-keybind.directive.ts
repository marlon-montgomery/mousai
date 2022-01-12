import { Directive, ElementRef, EventEmitter, OnDestroy, OnInit, Output } from '@angular/core';
import { fromEvent, Subscription } from 'rxjs';

@Directive({
    // tslint:disable-next-line:directive-selector
    selector: '[enterKeybind]',
})
export class EnterKeybindDirective implements OnInit, OnDestroy {
    @Output() enterPressed = new EventEmitter();
    private subscription: Subscription;

    constructor(private el: ElementRef) {}

    ngOnInit() {
        this.subscription = fromEvent(
            this.el.nativeElement,
            'keydown'
        ).subscribe((e: KeyboardEvent) => {
            if (e.keyCode === 13) {
                e.preventDefault();
                e.stopPropagation();
                this.el.nativeElement.blur();
                this.enterPressed.emit(e);
            }
        });
    }

    ngOnDestroy() {
        this.subscription.unsubscribe();
    }
}
