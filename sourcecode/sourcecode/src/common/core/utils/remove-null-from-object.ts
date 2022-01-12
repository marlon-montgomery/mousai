export function removeNullFromObject<T>(obj: T): T {
    const copy = {...obj};
    Object.keys(copy).forEach(key => {
        if (copy[key] == null || copy[key] === '') {
            delete copy[key];
        }
    });
    return copy;
}
