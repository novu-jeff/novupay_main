import $ from 'jquery';
import 'jquery';
import './bootstrap';
import 'bootstrap';
import 'boxicons'
import { fadeSplide, carouselSplide } from './autoplay';


if ($('.banner-images').length) {
    fadeSplide('.banner-images', 8000);
}
if ($('.payment-images-banks').length) {
    carouselSplide('.payment-images-banks', 0.5, 'rtl');
}
if ($('.payment-images-other-banks').length) {
    carouselSplide('.payment-images-other-banks', 0.5, 'ltr');
}

window.$ = window.jQuery = $;

$(function () {

    $(window).scroll(function () {
        if ($(this).scrollTop() > 100) {
            $(".scroll-top").css("display", "flex").fadeIn();
        } else {
            $(".scroll-top").fadeOut(function () {
                $(this).css("display", "none");
            });
        }
    });
    

    $('.scroll-top').click(function () {
        $('html, body').animate({ scrollTop: 0 }, 'slow');
    });
});
