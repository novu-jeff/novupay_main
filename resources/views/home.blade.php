@extends('layouts.app')

@section('base')
<div>
    <div class="base">
        <div class="overlay-top"></div>
        <div class="overlay-bottom"></div>
        <div class="container">
            <div class="landing-page">
                <div class="left-panel">
                    <div class="banner-text">
                        <small>NOVUPAY</small>
                        <h1>Secure & Seamless Digital Payments</h1>
                        <p>
                            Empower your business with NovuPay, a cutting-edge payment gateway by Novulutions Inc. Designed for businesses of all sizes, NovuPay ensures safe, smooth, and efficient digital transactions with a robust and scalable infrastructure. Experience the future of secure payment processing today!
                        </p>
                        <div class="actions">
                            <button class="btn-primary">Message Our Team</button>
                            <button class="btn-primary">Book Demo</button>
                        </div>
                    </div>
                </div>
                <div class="right-panel">
                    <div class="banner-images splide">
                        <div class="splide__track">
                              <ul class="splide__list">
                                    <li class="splide__slide">
                                        <img src="{{asset('images/banner1.png')}}" alt="banner">
                                    </li>
                                    <li class="splide__slide">
                                        <img src="{{asset('images/banner2.png')}}" alt="banner">
                                    </li>
                                    <li class="splide__slide">
                                        <img src="{{asset('images/banner3.png')}}" alt="banner">
                                    </li>
                              </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="scrollable-gesture">
                <div class="m-auto text-center">
                    <box-icon name='mouse'></box-icon>
                    <p>Scroll Down</p>
                </div>
            </div>
        </div>
    </div>
    <div class="payment-partners" id="payment-partners">
        <div class="section-title">
            Our Payment Partners
        </div>
        <div class="payment-images-banks splide">
            <div class="splide__track">
                <ul class="splide__list">
                    <?php foreach ($partners['banks'] as $image): ?>
                        <li class="splide__slide">
                            <img src="<?= asset('images/banks/' . $image->getFilename()) ?>" alt="Bank Image">
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <div class="payment-images-other-banks splide">
            <div class="splide__track">
                <ul class="splide__list">
                    <?php foreach ($partners['other_banks'] as $image): ?>
                        <li class="splide__slide">
                            <img src="<?= asset('images/other-banks/' . $image->getFilename()) ?>" alt="Bank Image">
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
    <div class="business-solutions" id="business-solutions">
        <div class="container">
            <div class="section-title">
                <div>Our Business Solutions</div>
                <div>Here’s what you’ll learn about us</div>
            </div>
            <div class="features">
                <div class="items">
                    <div>
                        <div class="icon mb-1">
                            <box-icon color='white' name='shield' ></box-icon>
                        </div>
                        <div class="title">
                            Secure & Reliable	
                        </div>
                        <hr class="my-2">
                        <div class="description">
                            NovuPay ensures high-level security with advanced fraud prevention, keeping transactions safe and reliable.
                        </div>
                    </div>
                </div>
                <div class="items active">
                    <div>
                        <div class="icon mb-1">
                            <box-icon color='dark' name='cog' ></box-icon>
                        </div>
                        <div class="title">
                            Seamless Integration
                        </div>
                        <hr class="my-2">
                        <div class="description">
                            Easily integrates with business and government financial systems for smooth digital transactions.
                        </div>
                    </div>
                </div>
                <div class="items">
                    <div>
                        <div class="icon mb-1">
                            <box-icon color='white' name='trending-up' ></box-icon>
                        </div>
                        <div class="title">
                            Scalable & Efficient	
                        </div>
                        <hr class="my-2">
                        <div class="description">
                            Designed to support businesses of all sizes with a robust and flexible payment infrastructure.
                        </div>
                    </div>
                </div>
                <div class="items">
                    <div>
                        <div class="icon mb-1">
                            <box-icon color='white' name='heart' ></box-icon>
                        </div>
                        <div class="title">
                            Compliance & Innovation	
                        </div>
                        <hr class="my-2">
                        <div class="description">
                            Meets industry standards while continuously evolving to enhance digital payment experiences.
                        </div>
                    </div>
                </div>
                <div class="items">
                    <div>
                        <div class="icon mb-1">
                            <box-icon color='white' name='like' ></box-icon>
                        </div>
                        <div class="title">
                            Multi-Channel Processing
                        </div>
                        <hr class="my-2">
                        <div class="description">
                            NovuPay supports multiple payment methods, including cards, e-wallets, bank transfers, and QR payments, enabling seamless transactions online or in person. Its real-time processing ensures fast settlements, reducing delays for both LGUs and constituents. With broad integration capabilities, it connects users to various financial services, enhancing efficiency and streamlining payment collection.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>    
@endsection