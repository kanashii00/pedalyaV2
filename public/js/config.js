/* ============================================
   PEDALYA - Laravel API & Configuration
   ============================================ */

const API_BASE_URL = window.location.origin + '/api';
const GOOGLE_MAPS_API_KEY = document.querySelector('meta[name="google-maps-key"]')?.content || '';
const POLL_INTERVAL = 30000;

const GEOFENCE_DEFAULTS = {
    centerLat: 14.5995,
    centerLng: 120.9842,
    radius: 2000
};

const RENTAL_DEFAULTS = {
    maxDuration: 12,
    ratePerHour: 15,
    lateFee: 25,
    maxOverdueBeforeLock: 2
};

const SAFETY_DEFAULTS = {
    accidentSensitivity: 2.5,
    autoLockOnTheft: true,
    buzzerWarningDuration: 30
};

async function apiRequest(endpoint, options = {}) {
    const url = API_BASE_URL + endpoint;
    const config = {
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            ...options.headers,
        },
        ...options,
    };

    if (config.body && typeof config.body === 'object') {
        config.body = JSON.stringify(config.body);
    }

    try {
        const response = await fetch(url, config);
        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.message || `HTTP ${response.status}`);
        }

        return data;
    } catch (error) {
        console.error(`API Error [${endpoint}]:`, error);
        throw error;
    }
}
