import AppCore from './core/App';
import ImageParallax from './animations/ImageParallax';

document.addEventListener('DOMContentLoaded', () => {
    // راه‌اندازی اسکرول نرم
    new AppCore();

    // راه‌اندازی انیمیشن پروژه‌ها
    new ImageParallax();
});