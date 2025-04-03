# **حسابیکس**

حسابیکس اولین نرم‌افزار حسابداری متن‌باز به زبان فارسی با رابط کاربری تحت وب است.
برای مشاهده نسخه نمایشی پروژه به وب‌سایت [https://hesabix.ir](https://hesabix.ir) مراجعه کنید.

## پیش‌نیازهای نصب

برای نصب هسته حسابیکس به ابزارهای زیر نیاز دارید:

* وب سرور: Apache، NginX و...
* پایگاه داده: Mysql، mariaDB، PostgreSql و...
* PHP: نسخه 8.1 به بالا
* افزونه‌های PHP: php-Intl، php-mbstring، php-http، php-raphf، php-zip، php-gd
* composer

## نصب

* پروژه را در پوشه وب سرور کپی یا کلون کنید. اگر از کنترل پنل‌های اشتراکی مانند cpanel یا directadmin استفاده می‌کنید، فایل‌ها را در پوشه اصلی کپی کنید و پوشه public_html بازنویسی خواهد شد.
* در سیستم مدیریت پایگاه داده خود یک پایگاه داده ایجاد کنید و فایل .env را در پوشه اصلی پروژه ویرایش کنید.
* وابستگی‌ها را با اجرای این دستور نصب کنید:

```
composer install
```

* فایل .env را ویرایش کرده و رشته اتصال پایگاه داده را با نام کاربری، رمز عبور و نام پایگاه داده خود تنظیم کنید.
* فایل محیط محلی را با اجرای این دستور ایجاد کنید:

```
composer dump-env prod
composer dump-env dev  // فقط برای محیط توسعه
```

* وارد سیستم مدیریت پایگاه داده خود مانند phpmyadmin شوید و فایل موجود در hesabixBackup/databaseFiles/hesabix-db-default.sql را وارد کنید.
* در خط فرمان به پوشه hesabixCore بروید و پایگاه داده را با این دستور به‌روز کنید:

```
php bin/console doctrine:schema:update --force --complete
```

آدرس دامنه اصلی را در مرورگر باز کنید، باید صفحه اصلی API حسابیکس را مشاهده کنید.

## اتصال به سرویس ایمیل

برای اتصال حسابیکس به سرویس ایمیل خود، فایل .env.local.php را در پوشه hesabixCore ویرایش کنید و رشته اتصال سرور ایمیل خود را در پارامتر MAILER_DSN تنظیم کنید. برای اطلاعات بیشتر درباره رشته‌های اتصال به مستندات symfony mailer مراجعه کنید. [اینجا کلیک کنید](https://symfony.com/doc/current/mailer.html#transport-setup)

پس از تنظیم رشته اتصال، فایل mailer.yaml را در پوشه configs ویرایش کرده و هدر برای ارسال ایمیل‌ها را تنظیم کنید.

## به‌روزرسانی نرم‌افزار

برای به‌روزرسانی نرم‌افزار با آخرین تغییرات منتشر شده در پوشه hesabixCore، این دستور را اجرا کنید:

```
php bin/console hesabix:update
```


## فرآیند ساخت رابط کاربری

## راه‌اندازی پروژه

```sh
npm install
```

### کامپایل و بارگذاری خودکار برای توسعه

```sh
npm run dev
npm run dev -- --host // برای استفاده در شبکه‌های خارجی
```

پس از اجرای این دستور می‌توانید با آدرس [http://localhost:5173](http://localhost:5173) در مرورگر خود دسترسی داشته باشید.

### پیکربندی آدرس API در حالت توسعه
به صورت پیش‌فرض، آدرس API در مسیر اصلی (/) و رابط کاربری وب در زیرپوشه /u/ قرار دارد.

مثال:
- آدرس API: https://myaddress.com
- آدرس رابط کاربری وب: https://myaddress.com/u/

اگر شما یک توسعه‌دهنده هستید و می‌خواهید با دستورات `npm run dev` یا `npm run dev --host` کار کنید، باید متغیری با نام `dev_api_url` تعریف کنید که آدرس API خارجی را مشخص می‌کند، مثلاً:

```js
return 'https://next.hesabix.ir';
```

### بررسی نوع، کامپایل و فشرده‌سازی برای تولید

```sh
npm run build-only
```

**تمام فایل‌های خروجی در پوشه public_html/u تولید خواهند شد که قابل اجرا روی وب سرور خواهند بود**

### مشارکت‌کنندگان

<a href="https://github.com/morrning/hesabixUI/graphs/contributors">
  <img src="https://contrib.rocks/image?repo=morrning/hesabixUI" />
</a>
<a href="https://github.com/morrning/hesabixCore/graphs/contributors">
  <img src="https://contrib.rocks/image?repo=morrning/hesabixCore" />
</a>
## حمایت مالی

برای حمایت مالی از توسعه‌دهندگان لطفاً از این لینک استفاده کنید:
[https://zarinp.al/hesabix.ir](https://zarinp.al/hesabix.ir)
