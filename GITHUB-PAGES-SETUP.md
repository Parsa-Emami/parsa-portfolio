# راه‌اندازی GitHub Pages

## آدرس نهایی

```text
https://parsa-emami.github.io/parsa-portfolio/
```

## فعال‌سازی یک‌باره Pages

پس از Push فایل‌های این بسته:

1. وارد Repository به نام `parsa-portfolio` شوید.
2. وارد `Settings` شوید.
3. از منوی سمت چپ `Pages` را باز کنید.
4. در بخش `Build and deployment`، گزینه `Source` را روی **GitHub Actions** قرار دهید.
5. وارد تب `Actions` شوید.
6. Workflow با نام **Deploy Portfolio to GitHub Pages** را باز کنید.
7. روی `Run workflow` بزنید و شاخه `main` را اجرا کنید.

از Pushهای بعدی، انتشار کاملاً خودکار خواهد بود.

## متغیرهای اختیاری Repository

مسیر:

```text
Settings → Secrets and variables → Actions → Variables
```

متغیرهای قابل تعریف:

| Variable | کاربرد |
|---|---|
| `PORTFOLIO_EMAIL` | ایمیل عمومی؛ فرم Pages برنامه ایمیل کاربر را باز می‌کند. |
| `STATIC_CONTACT_ENDPOINT` | آدرس سرویس فرم استاتیک مانند Formspree؛ در صورت تنظیم، فرم مستقیماً به آن ارسال می‌شود. |
| `GITHUB_PAGES_CNAME` | دامنه اختصاصی بدون `https://`، مانند `portfolio.example.com`. |

هیچ‌کدام برای اولین انتشار اجباری نیستند.

## به‌روزرسانی محتوا

بعد از ویرایش پروژه‌ها، مهارت‌ها، سوابق یا تنظیمات در پنل Laravel محلی:

```powershell
Set-Location "C:\Users\alienware\Documents\GitHub\parsa-portfolio"

php artisan portfolio:content-export --seed-if-empty

git add content app config resources tests deploy .github README.md GITHUB-PAGES-SETUP.md
git commit -m "Update portfolio content and GitHub Pages build"
git push origin main
```

Workflow به‌صورت خودکار خروجی جدید را منتشر می‌کند.

## نکته درباره پنل مدیریت

GitHub Pages فقط فایل استاتیک ارائه می‌کند. بنابراین در نسخه منتشرشده روی Pages:

- صفحات عمومی و Case Studyها فعال هستند.
- انیمیشن‌ها، گالری، SEO، Sitemap و حالت روشن/تاریک فعال هستند.
- پنل `/admin` اجرا نمی‌شود.
- دیتابیس، PHP، Queue و صندوق پیام روی Pages اجرا نمی‌شوند.

پنل Laravel همچنان در محیط Local یا در آینده روی VPS/cPanel قابل استفاده است؛ سپس خروجی عمومی آن با دستور `portfolio:content-export` برای Pages Snapshot می‌شود.

## رفع خطاهای متداول

### خطای Pages is not enabled

در `Settings → Pages`، Source را روی `GitHub Actions` قرار دهید و Workflow را دوباره اجرا کنید.

### سایت بدون CSS نمایش داده می‌شود

در Actions بررسی کنید مرحله `Build production assets` و سپس `Export Laravel pages` موفق باشند. مسیر Assetها به‌صورت خودکار از خروجی `configure-pages` ساخته می‌شود و نباید دستی به `/build` تغییر داده شود.

### تغییرات پنل روی Pages دیده نمی‌شود

پس از تغییرات پنل، حتماً اجرا کنید:

```powershell
php artisan portfolio:content-export
```

سپس فایل‌های `content/portfolio.json` و `content/media` را Commit و Push کنید.

### فرم تماس ارسال نمی‌شود

بدون Backend، فرم Pages یا ایمیل کاربر را باز می‌کند یا از `STATIC_CONTACT_ENDPOINT` استفاده می‌کند. برای صندوق پیام داخلی Laravel باید نسخه PHP روی یک سرور واقعی نیز Deploy شود.
