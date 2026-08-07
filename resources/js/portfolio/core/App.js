import Lenis from 'lenis';
import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger);

export default class App {
    constructor() {
        this.initLenis();
        this.initRAF();
        this.bindEvents();
    }

    initLenis() {
        this.lenis = new Lenis({
            duration: 1.2,
            easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)),
            direction: 'vertical',
            smooth: true,
        });

        // متوقف کردن اسکرول به صورت پیش‌فرض
        this.lenis.stop();

        this.lenis.on('scroll', ScrollTrigger.update);
    }

    initRAF() {
        gsap.ticker.add((time) => {
            this.lenis.raf(time * 1000);
        });
        gsap.ticker.lagSmoothing(0);
    }

    bindEvents() {
        // وقتی لودینگ تمام شد، اسکرول را فعال کن
        window.addEventListener('app:ready', () => {
            this.lenis.start();
            // رفرش کردن موقعیت‌های اسکرول به خاطر تغییرات DOM
            ScrollTrigger.refresh();
        });
    }
}