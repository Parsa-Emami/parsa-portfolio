import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

export default class ImageParallax {
    constructor() {
        this.images = document.querySelectorAll('[data-parallax]');
        if (this.images.length > 0) {
            this.init();
        }
    }

    init() {
        this.images.forEach((img) => {
            // افکت Reveal (نمایان شدن کادر عکس هنگام اسکرول به پایین)
            gsap.fromTo(img.parentElement, 
                { clipPath: "inset(100% 0% 0% 0%)" },
                { 
                    clipPath: "inset(0% 0% 0% 0%)", 
                    duration: 1.5, 
                    ease: "power4.out",
                    scrollTrigger: {
                        trigger: img.parentElement,
                        start: "top 85%",
                    }
                }
            );

            // افکت Parallax (حرکت عکس داخل کادر)
            gsap.to(img, {
                yPercent: 20,
                ease: "none",
                scrollTrigger: {
                    trigger: img.parentElement,
                    start: "top bottom",
                    end: "bottom top",
                    scrub: true,
                }
            });
        });
    }
}