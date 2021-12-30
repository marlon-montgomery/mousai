type Cache<T> = {
    expiresAt: number,
    value: T
};

const createCacheableValue = <T>(callback: () => Promise<T>, initialValue: T = null, expiresEvery: number = 5 * 60 * 1000) => {
    let cache: Cache<T> = {
        expiresAt: undefined,
        value: initialValue
    };

    const getCachedValue = async () => {
        const {expiresAt} = cache;
        const now = Date.now();

        if (typeof expiresAt === 'undefined' || expiresAt < now) {
            cache = {
                expiresAt: now + expiresEvery,
                value: await callback()
            };
        }

        return cache.value;
    };

    return {
        get value(): Promise<T> {
            return getCachedValue();
        }
    };
};

export default createCacheableValue;
