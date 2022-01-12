import {Injectable} from '@angular/core';
import {Keycodes} from './keycodes.enum';
import {fromEvent, Subscription} from 'rxjs';

interface ParsedKeybind {
    ctrl: boolean;
    shift: boolean;
    key: string;
}

// TODO: refactor so keybinds are stored for a specific "listenOn" element instead of globally

@Injectable({
    providedIn: 'root',
})
export class Keybinds {
    private bindings = [];

    private static bindingMatches(keybind: ParsedKeybind, e: KeyboardEvent) {
        return Keycodes[keybind.key.toUpperCase()] === e.keyCode &&
            (e.ctrlKey === keybind.ctrl || e.metaKey === keybind.ctrl) &&
            e.shiftKey === keybind.shift;
    }

    public add(keybinds: string|string[], callback: (e: KeyboardEvent) => void) {
        if ( ! Array.isArray(keybinds)) {
            keybinds = [keybinds];
        }
        keybinds.forEach(keybind => {
            this.bindings.push({keybind: this.parseKeybindString(keybind), keybindString: keybind, callback});
        });
    }

    public addWithPreventDefault(keybind: string, callback: () => any) {
        this.bindings.push({keybind: this.parseKeybindString(keybind), keybindString: keybind, callback, preventDefault: true});
    }

    public listenOn(el: HTMLElement|Document, options: {fireIfInputFocused?: boolean} = {}): Subscription {
        return fromEvent(el, 'keydown').subscribe((e: KeyboardEvent) => {
            if (options.fireIfInputFocused || !['input', 'select'].includes(document.activeElement.nodeName.toLowerCase())) {
                this.executeBindings(e);
            }
        });
    }

    private executeBindings(e: KeyboardEvent) {
        this.bindings.forEach(binding => {
            if ( ! Keybinds.bindingMatches(binding.keybind, e)) return;
            if (binding.preventDefault && e.preventDefault) e.preventDefault();
            binding.callback(e);
        });
    }

    /**
     * Parse keybind string into object.
     */
    private parseKeybindString(keybind: string): ParsedKeybind {
        const parts = keybind.trim().split('+');
        const parsed = {ctrl: false, shift: false, key: ''};

        parts.forEach(part => {
            part = part.trim().toLowerCase();

            if (part === 'ctrl') {
                parsed.ctrl = true;
            } else if (part === 'shift') {
                parsed.shift = true;
            } else {
                parsed.key = part;
            }
        });

        return parsed;
    }
}
