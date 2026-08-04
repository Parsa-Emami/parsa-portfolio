import './bootstrap';
import Lenis from 'lenis';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';


gsap.registerPlugin(ScrollTrigger);

document.documentElement.classList.add('js');

const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

if (!reduceMotion) {
    const lenis = new Lenis({
        duration: 1.05,
        smoothWheel: true,
        wheelMultiplier: 0.9,
        touchMultiplier: 1.1,
    });

    lenis.on('scroll', ScrollTrigger.update);

    gsap.ticker.add((time) => {
        lenis.raf(time * 1000);
    });

    gsap.ticker.lagSmoothing(0);

    gsap.from('[data-hero-line]', {
        yPercent: 120,
        rotate: 2,
        duration: 1.25,
        stagger: 0.12,
        ease: 'power4.out',
    });

    gsap.from('[data-orbit]', {
        scale: 0.65,
        opacity: 0,
        rotate: -20,
        duration: 1.4,
        delay: 0.45,
        ease: 'expo.out',
    });

    gsap.utils.toArray('[data-reveal]').forEach((element) => {
        gsap.from(element, {
            y: 36,
            opacity: 0,
            duration: 0.9,
            ease: 'power3.out',
            scrollTrigger: {
                trigger: element,
                start: 'top 88%',
                once: true,
            },
        });
    });

    gsap.utils.toArray('[data-project]').forEach((project) => {
        gsap.from(project, {
            y: 70,
            opacity: 0,
            scale: 0.985,
            duration: 1,
            ease: 'power3.out',
            scrollTrigger: {
                trigger: project,
                start: 'top 88%',
                once: true,
            },
        });
    });

    const marquee = document.querySelector('[data-marquee]');

    if (marquee) {
        gsap.to(marquee, {
            xPercent: -35,
            ease: 'none',
            scrollTrigger: {
                trigger: marquee,
                start: 'top bottom',
                end: 'bottom top',
                scrub: 1,
            },
        });
    }
}

const header = document.querySelector('[data-header]');
let lastScrollY = window.scrollY;

window.addEventListener('scroll', () => {
    const currentScrollY = window.scrollY;

    if (header) {
        header.classList.toggle('is-scrolled', currentScrollY > 24);
        header.classList.toggle('is-hidden', currentScrollY > lastScrollY && currentScrollY > 180);
    }

    lastScrollY = currentScrollY;
}, { passive: true });
