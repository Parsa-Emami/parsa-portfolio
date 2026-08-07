import AppCore from './core/App';
import Preloader from './components/Preloader';
import Cursor from './components/Cursor';
import ImageParallax from './animations/ImageParallax';
import TextReveal from './animations/TextReveal';

document.addEventListener('DOMContentLoaded', () => {
    // 1. راه‌اندازی هسته‌ها (این‌ها فورا اجرا میشن)
    new AppCore();    // Lenis در حالت Stop می‌ماند
    new Cursor();     // کرسر فعال می‌شود
    new Preloader();  // پرلودر شروع به شمردن و کنار رفتن می‌کند

    // 2. راه‌اندازی انیمیشن‌های نمایشی (صبر می‌کنند تا پرده کنار بره)
    window.addEventListener('app:ready', () => {
        
        // حالا متن‌های بخش Hero از پایین به بالا ظاهر می‌شن
        new TextReveal();
        
        // عکس‌های سکشن پروژه‌ها برای پارالاکس آماده می‌شن
        new ImageParallax();
        
    });
});