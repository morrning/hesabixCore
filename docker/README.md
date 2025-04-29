# راهنمای استقرار Hesabix با Docker

این راهنما به شما کمک می‌کند تا Hesabix را با استفاده از Docker مستقر کنید.

## پیش‌نیازها

- Docker
- Docker Compose

## مراحل نصب

1. ابتدا مخزن را کلون کنید:
```bash
git clone https://github.com/your-username/hesabix.git
cd hesabix
```

2. فایل‌های تنظیمات را کپی کنید:
```bash
cp .env.example .env
```

3. مقادیر مورد نظر خود را در فایل `.env` تنظیم کنید.

4. ساخت و اجرای کانتینرها:
```bash
docker-compose up -d
```

## دسترسی به سرویس‌ها

- وب‌سایت: http://localhost
- phpMyAdmin: http://localhost:8080

## اطلاعات ورود به دیتابیس

- نام کاربری: hesabix_user
- رمز عبور: hesabix_password (یا مقدار تعیین شده در فایل .env)
- نام دیتابیس: hesabix_db
- هاست: db
- پورت: 3306

## دستورات مفید

- مشاهده لاگ‌ها:
```bash
docker-compose logs -f
```

- توقف سرویس‌ها:
```bash
docker-compose down
```

- راه‌اندازی مجدد سرویس‌ها:
```bash
docker-compose restart
```

## نکات امنیتی

1. حتماً رمزهای عبور پیش‌فرض را در فایل `.env` تغییر دهید.
2. از SSL/TLS برای اتصالات استفاده کنید.
3. فایل‌های حساس را در `.gitignore` قرار دهید.

## پشتیبانی

برای گزارش مشکلات یا درخواست کمک، لطفاً یک issue در مخزن GitHub ایجاد کنید. 