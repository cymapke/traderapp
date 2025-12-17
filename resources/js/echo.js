// resources/js/echo.js
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

// Get CSRF token safely
function getCsrfToken() {
    // Method 1: Try to get from meta tag (if exists)
    const metaTag = document.querySelector('meta[name="csrf-token"]');
    if (metaTag) {
        return metaTag.getAttribute('content');
    }

    // Method 2: Try to get from cookie (Laravel default)
    const cookieValue = document.cookie
        .split('; ')
        .find(row => row.startsWith('XSRF-TOKEN='));

    if (cookieValue) {
        return decodeURIComponent(cookieValue.split('=')[1]);
    }

    // Method 3: Return null/empty string if not found
    console.warn('CSRF token not found. WebSocket authentication may fail.');
    return '';
}

// Get authentication token
function getAuthToken() {
    // Get from localStorage, sessionStorage, or wherever you store it
    return localStorage.getItem('token') || sessionStorage.getItem('token') || '';
}

// Initialize Echo
window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER || 'mt1',
    forceTLS: true,
    authEndpoint: '/broadcasting/auth',
    auth: {
        headers: {
            'Authorization': `Bearer ${getAuthToken()}`,
            'X-CSRF-TOKEN': getCsrfToken(),
        }
    }
});
