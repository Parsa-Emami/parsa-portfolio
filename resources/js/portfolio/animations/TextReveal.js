import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

export default class TextReveal {
    constructor() {
        this.elements = document.querySelectorAll('[data-reveal="text"]');
        if (this.elements.length > 0) {
            this.init();
        }
    }

    init() {
        this.elements.forEach((el) => {
            // 1. آماده‌سازی HTML: متن را داخل یک span پنهان‌کننده می‌گذاریم
            const text = el.innerText;
            el.innerHTML = '';
            
            const mask = document.createElement('span');
            mask.style.display = 'block';
            mask.style.overflow = 'hidden'; // ماسک
            
            const innerText = document.createElement('span');
            innerText.style.display = 'block';
            innerText.innerText = text;
            
            mask.appendChild(innerText);
            el.appendChild(mask);

            // 2. انیمیشن GSAP
            gsap.fromTo(innerText, 
                { y: '110%', rotation: 3 }, // شروع از پایین با کمی چرخش
                { 
                    y: '0%', 
                    rotation: 0,
                    duration: 1.2, 
                    ease: "power4.out",
                    scrollTrigger: {
                        trigger: el,
                        start: "top 90%", // وقتی المان وارد صفحه شد اجرا شود
                    }
                }
            );
        });
    }
}