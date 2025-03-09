import Splide from '@splidejs/splide';
import { AutoScroll } from '@splidejs/splide-extension-auto-scroll';

export function fadeSplide(selector, speed = 1) {
    const splide = new Splide(selector, {
        type: 'fade', 
        autoplay: true,
        interval: speed,
        pauseOnHover: false,
        pauseOnFocus: false,
        arrows: false,
        pagination: false,
        perPage: 1,
        rewind: true
    });

    splide.mount();
}


export function carouselSplide(selector, speed = 1, movement) {
    const defaultOptions = {
        type: 'loop',
        drag: false,
        focus: 'center',
        arrows: false,
        pagination: false, 
        speed: speed,
        perPage: 8,
        autoScroll: {
            speed: movement === 'rtl' ? -speed : speed, 
            pauseOnHover: false, 
            pauseOnFocus: false,
        },
        breakpoints: {
            1024: { perPage: 8 }, // Tablets
            768: { perPage: 2 },  // Small screens
            480: { perPage: 1 },  // Mobile devices
        }
    };

    const splide = new Splide(selector, { ...defaultOptions });
    splide.mount({ AutoScroll });
}


