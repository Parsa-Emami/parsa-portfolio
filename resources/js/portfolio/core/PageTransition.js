import barba from '@barba/core';
import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

export default class PageTransition {
    constructor(onPageReadyCallback) {
        // تابعی که بعد از لود صفحه جدید باید صدا زده شود تا انیمیشن‌ها دوباره فعال شوند
        this.onPageReady = onPageReadyCallback; 
        this.init();
    }

    init() {
        const self = this;

        barba.init({
            // جلوگیری از کلیک روی لینک‌های فعلی یا لینک‌های خارجی
            prevent: ({ el }) => el.classList.contains('no-barba') || el.host !== window.location.host,
            
            transitions: [{
                name: 'default-transition',
                
                // 1. زمانی که کاربر روی لینک کلیک می‌کند (خروج از صفحه فعلی)
                leave(data) {
                    return gsap.to('.c-transition-overlay', {
                        top: '0%', // پرده می‌آید روی صفحه
                        duration: 0.8,
                        ease: "power4.inOut"
                    });
                },
                
                // 2. زمانی که محتوای صفحه جدید از سرور گرفته شد
                enter(data) {
                    // الف) اسکرول را به بالای صفحه برمی‌گردانیم
                    if(window.siteCore && window.siteCore.lenis) {
                        window.siteCore.lenis.scrollTo(0, { immediate: true });
                    } else {
                        window.scrollTo(0, 0);
                    }

                    // ب) پاک کردن تمام ScrollTrigger های صفحه قبل (برای جلوگیری از باگ و نشتی مموری)
                    ScrollTrigger.getAll().forEach(trigger => trigger.kill());

                    // ج) پرده را به سمت بالا می‌کشیم تا صفحه جدید دیده شود
                    return gsap.to('.c-transition-overlay', {
                        top: '-100%', 
                        duration: 0.8,
                        ease: "power4.inOut",
                        onComplete: () => {
                            // بعد از اتمام، پرده را یواشکی می‌بریم پایین تا برای کلیک بعدی آماده باشد
                            gsap.set('.c-transition-overlay', { top: '100%' });
                        }
                    });
                },

                // 3. زمانی که انیمیشن ورود صفحه جدید تمام شد
                after(data) {
                    // اجرای مجدد انیمیشن‌های متن و عکس برای صفحه جدید
                    if (self.onPageReady) {
                        self.onPageReady();
                    }
                }
            }]
        });
    }
}