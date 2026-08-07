import AppCore from './core/App';
import ImageParallax from './animations/ImageParallax';
import TextReveal from './animations/TextReveal';
import Cursor from './components/Cursor';

document.addEventListener('DOMContentLoaded', () => {
    // 1. هسته (Lenis)
    new AppCore();

    // 2. اجزای سراسری (Cursor)
    new Cursor();

    // 3. انیمیشن‌ها
    new TextReveal();
    new ImageParallax();
});