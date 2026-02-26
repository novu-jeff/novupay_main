@extends('layouts.app')

@section('base')
<div class="page-section page-section--light">
    <div class="container page-section-inner">
        <div class="section-heading">
            <div class="section-kicker">About Novupay</div>
            <div class="section-title">A payment aggregator built for real-world collections</div>
            <div class="section-subtitle">
                Novupay is designed for organizations that need to accept payments across many rails while keeping operations, reporting, and compliance simple.
            </div>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-md-6">
                <div class="card-elevated h-100">
                    <div class="card-body p-4">
                        <h3 class="mb-3">
                            <box-icon name='target-lock' color='#0560aa'></box-icon>
                            <span class="ms-2">Our mission</span>
                        </h3>
                        <p class="mb-0">
                            To simplify and secure digital payments for LGUs, enterprises, and platforms by providing a single payment aggregation layer that
                            connects to banks, e‑wallets, and over‑the‑counter partners. We aim to make collections predictable, traceable, and easy to reconcile.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card-elevated h-100">
                    <div class="card-body p-4">
                        <h3 class="mb-3">
                            <box-icon name='show' color='#0560aa'></box-icon>
                            <span class="ms-2">Our vision</span>
                        </h3>
                        <p class="mb-0">
                            To be the trusted payment gateway and aggregator in the Philippines, powering digital collections for government and businesses of all
                            sizes with reliable infrastructure and thoughtful customer experiences.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-5">
            <h2 class="text-center mb-4">Why teams choose Novupay</h2>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="text-center p-3">
                        <div class="mb-3">
                            <box-icon name='shield' color='#0560aa' size='40px'></box-icon>
                        </div>
                        <h4>Secure &amp; compliant</h4>
                        <p class="mb-0">
                            Built with bank‑grade security practices and aligned with regulatory requirements for government and enterprise collections.
                        </p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="text-center p-3">
                        <div class="mb-3">
                            <box-icon name='cog' color='#0560aa' size='40px'></box-icon>
                        </div>
                        <h4>Single integration</h4>
                        <p class="mb-0">
                            Connect once to access multiple payment channels, with unified reporting and a consistent API surface.
                        </p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="text-center p-3">
                        <div class="mb-3">
                            <box-icon name='support' color='#0560aa' size='40px'></box-icon>
                        </div>
                        <h4>Partner support</h4>
                        <p class="mb-0">
                            A team that understands both technology and operations, ready to assist from onboarding to day‑to‑day incident handling.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-elevated" style="background: #f8f9fa;">
            <div class="card-body p-5">
                <h3 class="text-center mb-4">How Novupay fits into your stack</h3>
                <div class="row g-4">
                    <div class="col-md-6">
                        <h5 class="mb-2">For business &amp; product teams</h5>
                        <p class="mb-3">
                            Centralize payment performance, fees, and settlement insights across channels in one place. Create clearer customer journeys and reduce
                            friction at checkout, in portals, and in government service flows.
                        </p>
                    </div>
                    <div class="col-md-6">
                        <h5 class="mb-2">For developers &amp; IT</h5>
                        <p class="mb-0">
                            Integrate once and iterate. Use consistent webhooks, sandbox environments, and documentation to support new use cases
                            without re‑implementing each payment provider from scratch.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
