import AppCore from './core/App';
import Preloader from './components/Preloader';
import Cursor from './components/Cursor';
import ImageParallax from './animations/ImageParallax';
import TextReveal from './animations/TextReveal';
import PageTransition from './core/PageTransition';

document.addEventListener('DOMContentLoaded', () => {
    
    // 1. هسته‌هایی که فقط یکبار در کل لود سایت اجرا می‌شوند (چون بیرون از Container هستند)
    window.siteCore = new AppCore();
    new Cursor();
    new Preloader();

    // 2. تابعی برای اجرای انیمیشن‌های داخل صفحه
    const initPageComponents = () => {
        new TextReveal();
        new ImageParallax();
        // اگر کامپوننت‌های دیگری برای صفحه سینگل پروژه ساختید، اینجا اضافه کنید
    };

    // 3. راه‌اندازی پس از اتمام پرلودر اولیه
    window.addEventListener('app:ready', () => {
        
        // اجرای انیمیشن‌های صفحه اصلی برای اولین بار
        initPageComponents();
        
        // فعال کردن Barba.js برای کلیک‌های بعدی
        new PageTransition(() => {
            // این کال‌بک هر بار که به صفحه جدیدی می‌رویم اجرا می‌شود
            initPageComponents();
        });
        
    });
});