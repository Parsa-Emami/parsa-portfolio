import gsap from 'gsap';

export default class Cursor {
    constructor() {
        this.cursor = document.querySelector('.c-cursor');
        if (!this.cursor) return;

        // استفاده از quickTo برای پرفورمنس 60fps
        this.xSet = gsap.quickTo(this.cursor, "x", { duration: 0.4, ease: "power3" });
        this.ySet = gsap.quickTo(this.cursor, "y", { duration: 0.4, ease: "power3" });

        this.bindEvents();
    }

    bindEvents() {
        // حرکت موس
        window.addEventListener('mousemove', (e) => {
            // نمایان کردن کرسر در اولین حرکت
            if (this.cursor.style.opacity === '0' || this.cursor.style.opacity === '') {
                gsap.to(this.cursor, { opacity: 1, duration: 0.3 });
            }
            
            // مرکز کردن کرسر روی نقطه موس
            this.xSet(e.clientX - 6);
            this.ySet(e.clientY - 6);
        });

        // افکت هاور روی لینک‌ها و کارت‌های پروژه
        const hoverElements = document.querySelectorAll('a, button, .c-project');
        
        hoverElements.forEach((el) => {
            el.addEventListener('mouseenter', () => this.cursor.classList.add('is-hovering'));
            el.addEventListener('mouseleave', () => this.cursor.classList.remove('is-hovering'));
        });
    }
}