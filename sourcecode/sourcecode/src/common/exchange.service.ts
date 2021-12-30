import {Injectable} from '@angular/core';
import {AppHttpClient} from '@common/core/http/app-http-client.service';
import createCacheableValue from '../app/shared/createCacheableValue';

type ExchangeRateResponse = {
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

@Injectable({
    providedIn: 'root',
})
export class ExchangeService {
    protected cacheableValue = createCacheableValue(this.getExchangeRate);

    constructor(protected client: AppHttpClient) {
    }

    get exchangeRate(): Promise<ExchangeRateResponse> {
        return this.cacheableValue.value;
    }

    protected apiUrl(path: string, version: string = 'v0'): string {
        return `node/${version}/${path}`;
    }

    getExchangeRate(): Promise<ExchangeRateResponse> {
        const endpoint = this.apiUrl('get-exchange-rate');

        return this.client.get<ExchangeRateResponse>(endpoint, {}, {
            headers: {
                accept: 'application/json',
            }
        }).toPromise();
    }
}
