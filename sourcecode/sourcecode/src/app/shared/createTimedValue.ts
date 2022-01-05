import waitFor from './waitFor';

const createTimedValue = <T>(callback: () => Promise<T>, initialValue: T = null, reloadEvery: number = 5 * 60 * 1000) => {
    let initialized = false;
    let locked = false;

    let timer: number;
    let lastSuccessfulAttempt: number;

    let cache: T = initialValue;

    const updateCacheValue = async () => {
        if (locked) {
            const previousSuccessfulAttempt = lastSuccessfulAttempt;
            await waitFor(() => locked === false);

            if (previousSuccessfulAttempt !== lastSuccessfulAttempt) {
                return cache;
            }
        }

        try {
            locked = true;
            cache = await callback();

            lastSuccessfulAttempt = Date.now();
        } finally {
            locked = false;
        }
    };

    const setupTimer = () => timer = setInterval(() => updateCacheValue(), reloadEvery);

    return {
        async initialize() {
            initialized = true;
            return updateCacheValue().finally(setupTimer);
        },
        get value(): T | null {
            if (initialized === false) {
                throw new Error('timer is not initialized.');
            }

            return cache;
        },
        destroy() {
            initialized = false;
            clearInterval(timer);
            timer = undefined;
        }
    };
};

export default createTimedValue;
