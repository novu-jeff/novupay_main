import $ from 'jquery';
import './bootstrap';
import 'bootstrap';
import 'boxicons'
import { fadeSplide, carouselSplide } from './autoplay';


fadeSplide('.banner-images', 8000); 
carouselSplide('.payment-images-banks', 0.5, 'rtl'); 
carouselSplide('.payment-images-other-banks', 0.5, 'ltr'); 

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
