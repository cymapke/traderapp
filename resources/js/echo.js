// resources/js/echo.js
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

// Get authentication token safely
function getAuthToken() {
    return localStorage.getItem('token') || '';
}

// Get CSRF token safely (if needed)
function getCsrfToken() {
    const metaTag = document.querySelector('meta[name="csrf-token"]');
    return metaTag ? metaTag.content : '';
}

// Initialize Echo only if we have a token
const initEcho = () => {
    const token = getAuthToken();

    if (!token) {
        console.warn('No authentication token found for Echo');
        return null;
    }

    try {
        const echo = new Echo({
            broadcaster: 'pusher',
            key: import.meta.env.VITE_PUSHER_APP_KEY || 'dummy_key',
            cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER || 'mt1',
            forceTLS: true,
            authEndpoint: '/broadcasting/auth',
            auth: {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json',
                    // Only include X-CSRF-TOKEN if you're using session auth
                    // 'X-CSRF-TOKEN': getCsrfToken(),
                }
            },
            // Add these options to avoid 405 errors
            auth: {
                headers: {
                    'Authorization': `Bearer ${token}`,
                }
            },
            authorizer: (channel, options) => {
                return {
                    authorize: (socketId, callback) => {
                        // Use GET request instead of POST
                        axios.get('/broadcasting/auth', {
                            params: {
                                socket_id: socketId,
                                channel_name: channel.name
                            },
                            headers: {
                                'Authorization': `Bearer ${token}`,
                                'Accept': 'application/json'
                            }
                        })
                            .then(response => {
                                callback(false, response.data);
                            })
                            .catch(error => {
                                callback(true, error);
                            });
                    }
                };
            }
        });

        return echo;
    } catch (error) {
        console.error('Failed to initialize Echo:', error);
        return null;
    }
};

// Export initialized Echo instance
window.Echo = initEcho();
