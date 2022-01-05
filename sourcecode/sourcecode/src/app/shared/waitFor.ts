const waitFor = async (callback: () => boolean, timeout: number = 50) => new Promise<void>((resolve) => {
    const interval = setInterval(() => {
        if (callback() === true) {
            clearInterval(interval);
            resolve();
        }
    }, timeout);
});

export default waitFor;
