import axios from 'axios';

export const name = "hesabixConfig";

export function getApiUrl() {
    const origin = window.location.origin; // دامنه اصلی مثل http://localhost.com
    const path = window.location.pathname; // مسیر فعلی مثل /app/etc/u

    // مسیر رو به آرایه تبدیل می‌کنم تا بتونم پوشه‌ها رو جدا کنم
    const pathParts = path.split('/').filter(part => part !== ''); // ['app', 'etc', 'u']

    // پیدا کردن جایگاه u و حذفش به همراه هر چی بعدش هست
    const uIndex = pathParts.indexOf('u');
    if (uIndex !== -1) {
        // فقط مسیر تا قبل از u رو نگه می‌دارم
        const basePath = pathParts.slice(0, uIndex).join('/'); // app/etc
        if (basePath === '') {
            return `${origin}`;
        }
        return `${origin}/${basePath}`; // http://localhost.com/app/etc
    }

    // اگه u توی مسیر نبود، مسیر روت رو برگشت بده
    return `${origin}`;
}

export async function getSiteName() {
    // کلید ذخیره‌سازی در localStorage
    const localStorageKey = 'hesabix_site_name';

    // چک کن که آیا نام برنامه توی localStorage هست یا نه
    const storedName = localStorage.getItem(localStorageKey);
    if (storedName) {
        return storedName; // اگه بود، همون رو برگشت بده
    }

    try {
        // اگه نبود، درخواست به سمفونی ارسال کن
        const response = await axios.get(`${getApiUrl()}/system/getname`);
        const siteName = response.data; // فرض می‌کنم سمفونی نام رو مستقیم برمی‌گردونه

        // ذخیره توی localStorage
        localStorage.setItem(localStorageKey, siteName);
        return siteName;
    } catch (error) {
        console.error('خطا در گرفتن نام برنامه از سرور:', error);
        // اگه خطایی بود، یه مقدار پیش‌فرض برگشت بده
        return 'حسابیکس';
    }
}

export function getVersionCheckerUrl() {
    return 'https://api.hesabix.ir/clude/last-version';
}