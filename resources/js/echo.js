import Echo from '@ably/laravel-echo';
import * as Ably from 'ably';

window.Ably = Ably;

try {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    window.Echo = new Echo({
        broadcaster: 'ably',
        authEndpoint: '/broadcasting/auth',
        auth: {
            headers: csrfToken ? {
                'X-CSRF-TOKEN': csrfToken,
            } : {},
        },
    });

    // Log connection state for debugging
    if (window.Echo?.connector?.ably?.connection) {
        window.Echo.connector.ably.connection.on((stateChange) => {
            console.log('Ably connection state:', stateChange.current, stateChange.reason);
            if (stateChange.current === 'failed') {
                console.error('Ably connection failed:', stateChange.reason);
            }
        });
    }
} catch (error) {
    console.error('Echo initialization failed:', error);
    window.Echo = null;
}
