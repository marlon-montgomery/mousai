export function identityLogin(accessLevel) {
    let identityWindow = null;
    let initialized = false;
    let iframe = null;
    let user = null;

    return new Promise((resolve, reject) => {
        function login() {
            const h = 600;
            const w = 800;
            const y = window.outerHeight / 2 + window.screenY - h / 2;
            const x = window.outerWidth / 2 + window.screenX - w / 2;

            identityWindow = window.open(
                `https://identity.bitclout.com/log-in?accessLevelRequest=${accessLevel}`,
                null,
                `toolbar=no, width=${w}, height=${h}, top=${y}, left=${x}`,
            );
        }

        function handleInitialize(event) {
            if (!initialized) {
                initialized = true;
                iframe = event.source;
            }
            event.source.postMessage({id: event.data.id, service: 'identity'}, '*')
        }

        function handleLogin(payload) {
            user = {
                ...payload.users[payload['publicKeyAdded']],
                publicKey: payload['publicKeyAdded']
            }
            if (identityWindow) {
                iframe.postMessage({
                    id: Math.random().toString(36).substr(2, 5),
                    service: 'identity',
                    method: 'jwt',
                    payload: {
                        accessLevel: user['accessLevel'],
                        accessLevelHmac: user['accessLevelHmac'],
                        encryptedSeedHex: user['encryptedSeedHex'],
                    }
                }, '*');
            }
        }

        function handleJWT(payload) {
            user.jwt = payload['jwt'];
            if (identityWindow) {
                identityWindow.close();
                identityWindow = null;
            }
            resolve(user);
        }

        window.addEventListener('message', event => {
            const {data: {_, service, method, payload}} = event;

            if (service !== 'identity') {
                return;
            }

            if (method === 'initialize') {
                handleInitialize(event);
            } else if (method === 'login') {
                handleLogin(payload);
            } else if ('jwt' in payload) {
                handleJWT(payload);
            }
        });

        login()
    });
}
