import gsap from 'gsap';

export default class Preloader {
    constructor() {
        this.preloader = document.querySelector('.c-preloader');
        this.counter = document.querySelector('.c-preloader__counter');
        
        if (!this.preloader) return;
        this.init();
    }

    init() {
        // ایجاد یک آبجکت مجازی برای انیمیت کردن درصد
        let progress = { value: 0 };
        
        // یک تایم‌لاین می‌سازیم
        const tl = gsap.timeline({
            onComplete: () => {
                // شلیک ایونت به کل سایت: لودینگ تمام شد!
                window.dispatchEvent(new Event('app:ready'));
                
                // برداشتن قفل اسکرول
                document.body.classList.remove('is-loading');
                
                // مخفی کردن کامل پرلودر از DOM
                gsap.set(this.preloader, { display: 'none' });
            }
        });

        // 1. انیمیت کردن اعداد از 0 تا 100
        tl.to(progress, {
            value: 100,
            duration: 2, // 2 ثانیه زمان لودینگ
            ease: "power2.inOut",
            onUpdate: () => {
                this.counter.innerText = Math.round(progress.value) + '%';
            }
        })
        // 2. بالا رفتن و غیب شدن پرده
        .to(this.preloader, {
            yPercent: -100,
            duration: 1.2,
            ease: "power4.inOut"
        }, "+=0.2"); // با دو دهم ثانیه تاخیر بعد از رسیدن به 100%
    }
}