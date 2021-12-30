import {Subject} from 'rxjs';
import {v4 as uuid} from 'uuid';
import {first} from 'rxjs/operators';

import {DOCUMENT} from '@angular/common';
import {Inject, Injectable} from '@angular/core';

type Request = { id?: string, method: string };
type Response<T = { [key: string]: any }> = { id: string, payload?: T };

type Info = Response<{ hasStorageAccess: boolean, browserSupported: boolean }>;

@Injectable({
    providedIn: 'root',
})
export class BitcloutService {
    protected frame: HTMLIFrameElement;

    protected allowedOrigins: string[] = ['https://identity.bitclout.com'];

    protected response: Subject<any> = new Subject<any>();

    protected hasStorageAccess: boolean = null;
    protected isASupportedBrowser: boolean = null;

    public supported: Subject<boolean> = new Subject();

    get requiresStorageAccess(): boolean {
        return !this.hasStorageAccess;
    }

    set requiresStorageAccess(value: boolean) {
        this.hasStorageAccess = !value;
        this.frame.style.display = value ? 'block' : 'none';
    }

    get browserSupported(): boolean {
        return this.isASupportedBrowser;
    }

    set browserSupported(value: boolean) {
        this.isASupportedBrowser = value;

        this.supported.next(value);
        this.supported.complete();
    }

    constructor(@Inject(DOCUMENT) protected document: HTMLDocument) {
    }

    async initialize(): Promise<HTMLIFrameElement> {
        return new Promise(resolve => {
            const element = this.document.createElement('iframe');

            element.onload = () => resolve(element);
            element.onerror = () => window.location.reload();

            element.id = 'identity';

            element.style.top = '0';
            element.style.left = '0';
            element.style.right = '0';
            element.style.bottom = '0';
            element.style.width = '100vw';
            element.style.border = 'none';
            element.style.zIndex = '9999';
            element.style.height = '100vh';
            element.style.display = 'none';
            element.style.position = 'fixed';

            element.src = 'https://identity.bitclout.com/embed';

            this.frame = element;
            this.document.body.appendChild(element);

            window.addEventListener('message', value => this.onMessageHandler(value));
        });
    }

    sendMessage<T extends Request | Response>(value: T): T {
        const data: T & { service: string, id: string } = {
            service: 'identity',
            id: uuid(),
            ...value,
        };

        this.frame.contentWindow.postMessage(data, '*');

        return data;
    }

    waitForResponse<J extends Response, T extends Request = Request>(value: T): Promise<J> {
        return new Promise((resolve) => {
            this.response.pipe(first(data => data.id === value.id)).subscribe(data => resolve(data));
        });
    }

    async onInitialized({data: {id}}: MessageEvent) {
        if (typeof id !== 'string') return;
        this.sendMessage({id});

        const request = this.sendMessage({method: 'info'});

        const {
            payload: {
                browserSupported,
                hasStorageAccess
            }
        } = await this.waitForResponse<Info>(request);

        this.browserSupported = browserSupported;
        this.requiresStorageAccess = !hasStorageAccess;
    }

    onStorageGranted() {
        this.requiresStorageAccess = false;
    }

    onMessageHandler(event: MessageEvent) {
        const {data: {method, service}, origin} = event;

        const fromAnAllowedOrigin = this.allowedOrigins.includes(origin);
        const isFromIdentityService = service === 'identity';

        if (fromAnAllowedOrigin && isFromIdentityService) {
            const hasMethod = typeof method === 'string';
            if (hasMethod) {
                const handlers = {
                    initialize: (value: MessageEvent) => this.onInitialized(value),
                    storageGranted: () => this.onStorageGranted()
                };

                const handler = handlers[method];
                if (!handler) throw new Error(`Unrecognized method: ${method}. Supported values are: ${Object.keys(handlers).join(', ')}`);

                handler(event);
            } else {
                this.response.next(event.data);
            }
        }
    }
}
