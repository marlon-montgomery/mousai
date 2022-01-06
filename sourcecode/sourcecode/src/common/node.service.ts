import {Injectable} from '@angular/core';
import {AppHttpClient} from '@common/core/http/app-http-client.service';
import createTimedValue from '../app/shared/createTimedValue';
import waitFor from '../app/shared/waitFor';
import {BitcloutService} from '@common/auth/bitclout.service';
import {CurrentUser} from '@common/auth/current-user';

type Node = {
    Name: string
    Owner: string
    URL: string
};

export type AppStateResponse = {
    BlockHeight: number
    BuyETHAddress: string
    BuyWithETH: boolean
    CompProfileCreation: boolean
    CountrySignUpBonus: {
        AllowCustomKickbackAmount: boolean
        AllowCustomReferralAmount: boolean
        KickbackAmountOverrideUSDCents: number
        ReferralAmountOverrideUSDCents: number
    }
    CreateProfileFeeNanos: number
    DefaultFeeRateNanosPerKB: number
    DiamondLevelMap: { [key: number]: number }
    HasJumioIntegration: boolean
    HasStarterDeSoSeed: boolean
    HasTwilioAPIKey: boolean
    HasWyreIntegration: boolean
    IsTestnet: boolean
    JumioBitCloutNanos: number
    JumioDeSoNanos: number
    JumioUSDCents: number
    MinSatoshisBurnedForProfileCreation: number
    Nodes: { [key: number]: Node }
    TransactionFeeMap: { [key: string]: null }
    USDCentsPerBitCloutExchangeRate: number
    USDCentsPerDeSoExchangeRate: number
};

export type ExchangeRateResponse = {
    BuyDeSoFeeBasisPoints: number,
    NanosPerETHExchangeRate: number,
    NanosSold: number,
    SatoshisPerBitCloutExchangeRate: number,
    SatoshisPerDeSoExchangeRate: number,
    USDCentsPerBitCloutExchangeRate: number,
    USDCentsPerBitCloutReserveExchangeRate: number,
    USDCentsPerBitcoinExchangeRate: number,
    USDCentsPerDeSoExchangeRate: number,
    USDCentsPerDeSoReserveExchangeRate: number,
    USDCentsPerETHExchangeRate: number,
};

export type SendDesoRequest = {
    senderPublicKeyBase58Check: string,
    recipientPublicKeyOrUsername: string,
    amountNanos: number,
    minFeeRateNanosPerKB?: number,
};

export type TxInput = {
    TxID: number[],
    Index: number
};

export type TxOutput = {
    PublicKey: string,
    AmountNanos: number
};

export type Transaction = {
    TxInputs: TxInput[],
    TxOutputs: TxOutput[],
    TxnMeta: object,
    PublicKey: string,
    ExtraData: null,
    Signature: null,
    TxnTypeJSON: number
};

export type SendDesoResponse = {
    ChangeAmountNanos: number
    FeeNanos: number
    SpendAmountNanos: number
    TotalInputNanos: number
    Transaction: Transaction,
    TransactionHex: string
    TransactionIDBase58Check: string
    TxnHashHex: string
};

@Injectable({
    providedIn: 'root',
})
export class NodeService {
    readonly NANOS_PER_UNIT = 1e9;
    readonly DEFAULT_FEE_RATE_NANOS_PER_KB = 1000.0;

    readonly FEE_RATE_DESO_PER_KB = (this.DEFAULT_FEE_RATE_NANOS_PER_KB / this.NANOS_PER_UNIT).toFixed(9);
    readonly FEE_RATE_NANOS_PER_KB = Math.floor(parseFloat(this.FEE_RATE_DESO_PER_KB) * 1e9);

    readonly ROUTE_SEND_DESO = 'send-deso';
    readonly ROUTE_GET_APP_STATE = 'get-app-state';
    readonly ROUTE_GET_EXCHANGE_RATE = 'get-exchange-rate';
    readonly ROUTE_SUBMIT_TRANSACTION = 'submit-transaction';

    readonly DEFAULT_OPTIONS = {
        headers: {
            accept: 'application/json'
        }
    };

    protected cache = {
        appState: createTimedValue(() => this.getAppState()),
        exchangeRate: createTimedValue(() => this.getExchangeRate()),
    };

    protected initialized = false;

    public nodeUrl(path?: string): string {
        return `https://bitclout.com/${path}`;
    }

    protected apiUrl(path: string, version: string = 'v0'): string {
        return `https://deso.mousai.stream/api/${version}/${path}`;
    }

    constructor(
        protected currentUser: CurrentUser,
        protected client: AppHttpClient,
        protected identity: BitcloutService,
    ) {
        const promises = Object.entries(this.cache).map(([, item]) => item.initialize());
        Promise.all<any>(promises).finally(() => this.initialized = true);
    }

    get ApplicationState(): AppStateResponse | null {
        return this.cache.appState.value;
    }

    get ExchangeRate(): ExchangeRateResponse | null {
        return this.cache.exchangeRate.value;
    }

    async makeSureServiceIsInitialized(): Promise<boolean> {
        await waitFor(() => this.initialized);
        return true;
    }

    protected getAppState() {
        const endpoint = this.apiUrl(this.ROUTE_GET_APP_STATE);

        return this.client.post<AppStateResponse>(endpoint, {}, this.DEFAULT_OPTIONS).toPromise();
    }

    protected getExchangeRate(): Promise<ExchangeRateResponse> {
        const endpoint = this.apiUrl(this.ROUTE_GET_EXCHANGE_RATE);

        return this.client.get<ExchangeRateResponse>(endpoint, {}, this.DEFAULT_OPTIONS).toPromise();
    }

    protected formatCents(value: number) {
        if (value < 1) return '<$0.01';

        return `$${(value / 100).toFixed(2)}`;
    }

    getNanosForDiamond(index: number): number {
        const {DiamondLevelMap} = this.ApplicationState ?? {};

        if (typeof DiamondLevelMap !== 'undefined') {
            return DiamondLevelMap[index];
        }

        return undefined;
    }

    getCentsForNanos(value: number) {
        const {USDCentsPerBitCloutExchangeRate} = this.ExchangeRate ?? {};
        const valueInCents = value * USDCentsPerBitCloutExchangeRate / this.NANOS_PER_UNIT;

        return this.formatCents(valueInCents);
    }

    getUSDForDiamond(index: number): string {
        const {DiamondLevelMap} = this.ApplicationState ?? {};
        const {USDCentsPerBitCloutExchangeRate} = this.ExchangeRate ?? {};

        if (typeof DiamondLevelMap === 'undefined' || typeof USDCentsPerBitCloutExchangeRate === 'undefined') {
            return '';
        }

        const nanosForIndex = DiamondLevelMap[index];

        return this.getCentsForNanos(nanosForIndex);
    }

    protected async submitTransaction(signedTransactionHex: string) {
        const endpoint = this.apiUrl(this.ROUTE_SUBMIT_TRANSACTION);

        await this.client.post<void>(endpoint, {
            TransactionHex: signedTransactionHex
        }).toPromise();
    }

    protected async createTransaction(request: SendDesoRequest) {
        const {
            senderPublicKeyBase58Check,
            recipientPublicKeyOrUsername,
            amountNanos,
            minFeeRateNanosPerKB = this.FEE_RATE_NANOS_PER_KB
        } = request;
        const endpoint = this.apiUrl(this.ROUTE_SEND_DESO);

        return this.client.post<SendDesoResponse>(endpoint, {
            SenderPublicKeyBase58Check: senderPublicKeyBase58Check,
            RecipientPublicKeyOrUsername: recipientPublicKeyOrUsername,
            AmountNanos: amountNanos,
            MinFeeRateNanosPerKB: minFeeRateNanosPerKB,
        }).toPromise();
    }

    createNotification(request: SendDesoRequest) {
        const donor = this.currentUser.get('display_name');
        const donee = request.recipientPublicKeyOrUsername;

        const amount = request.amountNanos;
        const amountUSD = this.getCentsForNanos(amount);

        return this.client.post(`bitclout/on-tip`, {
            donor, donee,
            amount_usd: amountUSD,
            amount: (amount / this.NANOS_PER_UNIT).toFixed(5),
        }).toPromise();
    }

    async sendDeso(request: SendDesoRequest) {
        const transaction = await this.createTransaction({
            minFeeRateNanosPerKB: this.FEE_RATE_NANOS_PER_KB,
            ...request,
        });

        const {payload: {signedTransactionHex}} = await this.identity.sign({
            transactionHex: transaction.TransactionHex,
            ...this.identity.paramsForPublicKey(request.senderPublicKeyBase58Check)
        });

        await this.submitTransaction(signedTransactionHex);
        return this.createNotification(request);
    }
}
