import {Subject} from 'rxjs';
import {v4 as uuid} from 'uuid';
import {first} from 'rxjs/operators';

import {DOCUMENT} from '@angular/common';
import {Inject, Injectable} from '@angular/core';

type Request = { id?: string, method: string };
type Response<T = { [key: string]: any }> = { id: string, payload?: T };

type Info = Response<{ hasStorageAccess: boolean, browserSupported: boolean }>;

export type User = {
    hasExtraText: boolean
    btcDepositAddress: string
    ethDepositAddress: string
    version: number
    encryptedSeedHex: string
    network: string
    accessLevel: number
    accessLevelHmac: string
};

export type SignRequest = {
    accessLevel: number;
    accessLevelHmac: string;
    encryptedSeedHex: string;
    transactionHex: string;
};

type Sign = Response<{ signedTransactionHex: string }>;

@Injectable({
    providedIn: 'root',
})
export class BitcloutService {
    protected static readonly KEY_USERS = 'deso.users';
    protected static readonly KEY_CURRENT_USER = 'deso.currentUser';
    protected static users: { [key: string]: User } = {};
    protected static currentUserPublicKey: string;

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

    static set CurrentUserPublicKey(value: string) {
        this.currentUserPublicKey = value;
        localStorage.setItem(this.KEY_CURRENT_USER, value);
    }

    static get CurrentUserPublicKey() {
        if (typeof this.currentUserPublicKey === 'undefined') {
            const value = localStorage.getItem(this.KEY_CURRENT_USER);
            const [publicKey] = Object.keys(this.Users) ?? [];

            if (value === null && typeof publicKey === 'undefined') {
                return null;
            }

            if (value === null) {
                this.CurrentUserPublicKey = publicKey;
            }

            this.currentUserPublicKey = value === null ? publicKey : value;
        }

        return this.currentUserPublicKey;
    }

    static get CurrentUser() {
        return this.Users[this.CurrentUserPublicKey];
    }

    static set Users(value: { [key: string]: User }) {
        this.users = value;
        localStorage.setItem(this.KEY_USERS, JSON.stringify(value));
    }

    static get Users() {
        if (Object.keys(this.users).length === 0) {
            const value = localStorage.getItem(this.KEY_USERS);
            this.users = value === null ? {} : JSON.parse(value);
        }

        return this.users;
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
                    storageGranted: () => this.onStorageGranted(),
                    sign: () => this.response.next(event.data)
                };

                const handler = handlers[method];
                if (!handler) throw new Error(`Unrecognized method: ${method}. Supported values are: ${Object.keys(handlers).join(', ')}`);

                handler(event);
            } else {
                this.response.next(event.data);
            }
        }
    }

    sign(payload: SignRequest): Promise<Sign> {
        const request = this.sendMessage({method: 'sign', payload});
        return this.waitForResponse<Sign>(request);
    }

    paramsForPublicKey(publicKey: string) {
        const {
            encryptedSeedHex,
            accessLevel,
            accessLevelHmac
        } = BitcloutService.Users[publicKey];

        return {encryptedSeedHex, accessLevel, accessLevelHmac};
    }
}
