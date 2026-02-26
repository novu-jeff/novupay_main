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
                            Empower your business with NovuPay, a cutting-edge payment gateway. Designed for businesses of all sizes, NovuPay ensures safe, smooth, and efficient digital transactions with a robust and scalable infrastructure. Experience the future of secure payment processing today!
                        </p>
                        <div class="actions">
                            <a href="{{ route('contact') }}" class="btn-primary">Message Our Team</a>
                            <a href="{{ route('demo') }}" class="btn-primary btn-outline">Book Demo</a>
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
    {{-- Temporarily hidden: Our Payment Partners section --}}
    {{--
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
    --}}
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
    <div class="about-section" id="about">
        <div class="container">
            <div class="section-heading">
                <div class="section-kicker">About Novupay</div>
                <div class="section-title">Payment aggregation for modern teams</div>
                <div class="section-subtitle">
                    Novupay unifies banks, e‑wallets, and over‑the‑counter channels into a single, secure payment layer for your business and government collections.
                </div>
            </div>

            <div class="about-grid">
                <div class="about-copy">
                    <p class="lead">
                        Built as a payment aggregator, Novupay connects you to a growing network of banks and alternative payment channels through one contract,
                        one integration, and one reconciliation flow. We handle the complexity of routing, settlement, and reporting so you can focus on the
                        experiences that matter to your customers and constituents.
                    </p>

                    <div class="about-badges">
                        <span class="pill">Unified payment gateway</span>
                        <span class="pill">Banks &amp; e‑wallets</span>
                        <span class="pill">Government-ready compliance</span>
                    </div>

                    <div class="about-metadata">
                        <div class="meta-item">
                            <div class="label">Primary focus</div>
                            <div class="value">Collections for LGUs &amp; enterprises</div>
                        </div>
                        <div class="meta-item">
                            <div class="label">Payment methods</div>
                            <div class="value">Cards, QR, bank transfers, e‑wallets</div>
                        </div>
                        <div class="meta-item">
                            <div class="label">Key capabilities</div>
                            <div class="value">Aggregation • Routing • Reporting</div>
                        </div>
                    </div>

                    <div class="about-steps">
                        <div class="step">
                            <div class="step-icon">1</div>
                            <div class="step-body">
                                <div class="step-title">Connect once</div>
                                <div class="step-text">
                                    Integrate a single API instead of building and maintaining multiple bank or e‑wallet connections.
                                </div>
                            </div>
                        </div>
                        <div class="step">
                            <div class="step-icon">2</div>
                            <div class="step-body">
                                <div class="step-title">Offer more ways to pay</div>
                                <div class="step-text">
                                    Turn on additional channels behind the same experience—cards, QR, bank transfers, and more.
                                </div>
                            </div>
                        </div>
                        <div class="step">
                            <div class="step-icon">3</div>
                            <div class="step-body">
                                <div class="step-title">Reconcile faster</div>
                                <div class="step-text">
                                    See status updates and settlement insights in one place, aligned with how your finance team works.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="about-panel">
                    <div class="about-panel-inner">
                        <div class="panel-title">Aggregation at the core</div>
                        <div class="panel-subtitle">
                            A single platform to accept, track, and reconcile payments across multiple rails.
                        </div>

                        <div class="panel-stats">
                            <div class="stat">
                                <div class="stat-label">Channels supported</div>
                                <div class="stat-value">Multi‑rail</div>
                            </div>
                            <div class="stat">
                                <div class="stat-label">Integration</div>
                                <div class="stat-value">Single API</div>
                            </div>
                            <div class="stat">
                                <div class="stat-label">Use cases</div>
                                <div class="stat-value">LGUs &amp; B2B</div>
                            </div>
                            <div class="stat">
                                <div class="stat-label">Experience</div>
                                <div class="stat-value">Real‑time status</div>
                            </div>
                        </div>

                        <div class="panel-footnote">
                            This is a high‑level overview. Specific bank, e‑wallet, and counter partners can be tailored per implementation and compliance requirements.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>    
@endsection

