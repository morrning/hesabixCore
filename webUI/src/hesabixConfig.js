import axios from 'axios';

// ثابت‌ها
const KEYS = {
    DEV_API_URL: 'dev_api_url',
    DEV_ALERT_SHOWN: 'dev_mode_alert_shown',
    SITE_NAME: 'hesabix_site_name',
    SITE_SLOGON: 'hesabix_site_slogon'
};

const DEFAULTS = {
    SITE_NAME: 'حسابیکس',
    SITE_SLOGON: 'حسابیکس سامانه جامع مدیریت کسب‌و‌کار'
};

export const name = "hesabixConfig";

// کش برای API URL
let cachedApiUrl = null;

export function getApiUrl() {
    if (cachedApiUrl) return cachedApiUrl;

    const devApiUrl = localStorage.getItem(KEYS.DEV_API_URL);
    const alertShown = localStorage.getItem(KEYS.DEV_ALERT_SHOWN);

    if (devApiUrl) {
        if (!alertShown) {
            alert(`شما در حالت توسعه هستید و به آدرس زیر متصل می‌شوید:\n${devApiUrl}`);
            localStorage.setItem(KEYS.DEV_ALERT_SHOWN, 'true');
        }
        cachedApiUrl = devApiUrl;
        return devApiUrl;
    }

    const origin = window.location.origin;
    const pathParts = window.location.pathname.split('/').filter(part => part !== '');
    const uIndex = pathParts.indexOf('u');

    if (uIndex !== -1) {
        const basePath = pathParts.slice(0, uIndex).join('/');
        cachedApiUrl = basePath ? `${origin}/${basePath}` : origin;
    } else {
        cachedApiUrl = origin;
    }

    return cachedApiUrl;
}

// تابع کمکی برای گرفتن داده از سرور و کش کردن
async function fetchAndCache(url, localStorageKey, defaultValue) {
    const storedValue = localStorage.getItem(localStorageKey);
    if (storedValue) return storedValue;

    try {
        const response = await axios.get(url);
        if (response.status === 200) {
            const data = response.data;
            localStorage.setItem(localStorageKey, data);
            return data;
        }
        throw new Error('پاسخ نامعتبر از سرور');
    } catch (error) {
        console.error(`خطا در گرفتن داده از ${url}:`, error);
        return defaultValue;
    }
}

export async function getSiteName() {
    return fetchAndCache(
        `${getApiUrl()}/system/getname`,
        KEYS.SITE_NAME,
        DEFAULTS.SITE_NAME
    );
}

export async function getSiteSlogon() {
    return fetchAndCache(
        `${getApiUrl()}/system/getslogon`,
        KEYS.SITE_SLOGON,
        DEFAULTS.SITE_SLOGON
    );
}

export function getBasePath() {
    const fullPath = window.location.pathname;
    const uIndex = fullPath.indexOf('/u');
    return uIndex !== -1 ? fullPath.substring(0, uIndex + '/u'.length) + '/' : '/u/';
}

export function getVersionCheckerUrl() {
    return 'https://api.hesabix.ir/clude/last-version';
}