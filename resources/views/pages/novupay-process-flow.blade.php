@extends('layouts.app')

@section('title', 'NovuPay OPS Process Flow | NovuPay')
@section('meta_description', 'Presentation-ready NovuPay OPS process flow and standard flowchart covering payment initiation, partner routing, QRPh checkout generation, authorization, status handling, and reporting.')

@section('base')
<style>
    .process-page .process-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 24px;
    }

    .process-page .hero-stat-grid {
        display: grid;
        gap: 12px;
    }

    .process-page .flow-sequence {
        display: grid;
        gap: 14px;
    }

    .process-page .flow-step-card {
        display: grid;
        grid-template-columns: 52px minmax(0, 1fr);
        gap: 16px;
        padding: 18px 20px;
        border-radius: 18px;
        background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        border: 1px solid #e5eef9;
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.06);
    }

    .process-page .flow-step-no {
        width: 52px;
        height: 52px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 14px;
        background: linear-gradient(135deg, #0560aa 0%, #0f172a 100%);
        color: #fff;
        font-size: 20px;
        font-weight: 700;
    }

    .process-page .flow-step-title {
        font-size: 18px;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 6px;
    }

    .process-page .flow-step-copy {
        font-size: 14px;
        color: #4b5563;
        line-height: 1.7;
        margin-bottom: 0;
    }

    .process-page .flow-arrow {
        text-align: center;
        color: #94a3b8;
        font-size: 26px;
        line-height: 1;
    }

    .process-page .standards-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        overflow: hidden;
        border-radius: 16px;
    }

    .process-page .standards-table th,
    .process-page .standards-table td {
        padding: 16px 18px;
        vertical-align: top;
        border-bottom: 1px solid #e5e7eb;
    }

    .process-page .standards-table th {
        background: #eff6ff;
        color: #0f172a;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.08em;
    }

    .process-page .standards-table td {
        font-size: 14px;
        color: #4b5563;
        background: #fff;
    }

    .process-page .standards-table tr:last-child td {
        border-bottom: none;
    }

    .process-page .boundary-grid,
    .process-page .role-grid,
    .process-page .control-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 20px;
    }

    .process-page .callout-band {
        padding: 18px 20px;
        border-radius: 18px;
        background: linear-gradient(135deg, #0f172a 0%, #1d4ed8 100%);
        color: #fff;
    }

    .process-page .callout-band p,
    .process-page .callout-band h5,
    .process-page .callout-band span {
        color: #fff !important;
    }

    .process-page .callout-band p:last-child {
        margin-bottom: 0;
    }

    .process-page .flowchart-figure {
        padding: 24px;
        border-radius: 18px;
        background: linear-gradient(180deg, #f8fbff 0%, #eef5ff 100%);
        overflow-x: auto;
    }

    .process-page .flowchart-svg {
        width: 100%;
        min-width: 980px;
        height: auto;
        display: block;
    }

    @media (max-width: 991.98px) {
        .process-page .boundary-grid,
        .process-page .role-grid,
        .process-page .control-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 767.98px) {
        .process-page .flow-step-card {
            grid-template-columns: 1fr;
        }
    }

    @media print {
        .navbar,
        footer,
        .scroll-top,
        .process-page .process-actions,
        .doc-sidebar {
            display: none !important;
        }

        .page-section {
            padding: 24px 0 !important;
            background: #fff !important;
        }

        .doc-layout {
            display: block !important;
        }

        .doc-section,
        .card-elevated,
        .flow-step-card {
            break-inside: avoid;
            box-shadow: none !important;
        }
    }
</style>

<div class="page-section page-section--light process-page">
    <div class="container page-section-inner" style="max-width: 1160px;">
        <div class="section-heading" style="margin-bottom: 28px;">
            <div class="section-kicker">NovuPay process flow</div>
            <div class="section-title">Operator of Payment System standard process flow and flowchart</div>
            <div class="section-subtitle" style="max-width: 860px;">
                A presentation-ready reference for how NovuPay initiates, routes, monitors, and reports payment transactions while regulated institutions and payment partners retain authorization, clearing, and settlement responsibilities.
            </div>
        </div>

        <div class="card-elevated mb-5" style="border-radius: 20px; overflow: hidden;">
            <div class="card-body p-4 p-md-5" style="background: linear-gradient(135deg, #0f172a 0%, #0560aa 56%, #dbeafe 180%); color: #fff;">
                <div class="row g-4 align-items-center">
                    <div class="col-lg-8">
                        <div class="pill mb-3" style="background: rgba(255,255,255,0.14); color: #fff;">Formal OPS presentation for partners, participants, and reviewers</div>
                        <h1 class="whitepaper-hero-title" style="font-size: 40px; font-weight: 700; line-height: 1.15; margin-bottom: 16px;">
                            Standard transaction lifecycle from payment request creation to participant visibility
                        </h1>
                        <p style="font-size: 16px; line-height: 1.8; color: rgba(255,255,255,0.9); margin-bottom: 0;">
                            This page consolidates the NovuPay white paper and OPS application documentation into one clear operating model.
                            It shows where NovuPay performs orchestration, where regulated partners perform financial processing, and how transaction outcomes return into reporting, reconciliation, and participant-facing visibility.
                        </p>
                        <div class="process-actions">
                            <button type="button" class="btn btn-light" onclick="window.print()" style="padding: 10px 24px;">Print / Save as PDF</button>
                            <a href="{{ route('services.novupay.whitepaper') }}" class="btn btn-outline-light" style="padding: 10px 24px;">View white paper</a>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="hero-stat-grid">
                            <div style="padding: 16px 18px; border-radius: 16px; background: rgba(15,23,42,0.34); border: 1px solid rgba(255,255,255,0.18);">
                                <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.08em; opacity: 0.75;">Primary model</div>
                                <div style="font-size: 20px; font-weight: 700;">Payment orchestration</div>
                            </div>
                            <div style="padding: 16px 18px; border-radius: 16px; background: rgba(15,23,42,0.34); border: 1px solid rgba(255,255,255,0.18);">
                                <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.08em; opacity: 0.75;">Standard channel</div>
                                <div style="font-size: 20px; font-weight: 700;">QRPh-enabled flows</div>
                            </div>
                            <div style="padding: 16px 18px; border-radius: 16px; background: rgba(15,23,42,0.34); border: 1px solid rgba(255,255,255,0.18);">
                                <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.08em; opacity: 0.75;">Funds custody</div>
                                <div style="font-size: 20px; font-weight: 700;">Not held by NovuPay</div>
                            </div>
                            <div style="padding: 16px 18px; border-radius: 16px; background: rgba(15,23,42,0.34); border: 1px solid rgba(255,255,255,0.18);">
                                <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.08em; opacity: 0.75;">Outcome visibility</div>
                                <div style="font-size: 20px; font-weight: 700;">Redirects, APIs, webhooks</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="doc-layout">
            <aside class="doc-sidebar">
                <div class="doc-toc-title">On this page</div>
                <ul>
                    <li><a href="#overview">Overview</a></li>
                    <li><a href="#process-flow">Process flow</a></li>
                    <li><a href="#flowchart">Standard flowchart</a></li>
                    <li><a href="#steps">Step-by-step model</a></li>
                    <li><a href="#roles">Roles and boundaries</a></li>
                    <li><a href="#architecture">Architecture alignment</a></li>
                    <li><a href="#controls">Controls and resilience</a></li>
                    <li><a href="#summary">Summary</a></li>
                </ul>
            </aside>

            <div class="doc-content">
                <section id="overview" class="doc-section">
                    <h2>1. Overview</h2>
                    <div class="doc-section-kicker">The operating model in one view</div>
                    <p>
                        NovuPay operates as an Operator of Payment System that enables payment initiation, request generation, routing, transaction monitoring, and reporting.
                        It acts as the orchestration layer between initiating participants and regulated payment infrastructure, while authorization, clearing, settlement, and custody remain outside NovuPay.
                    </p>
                    <div class="card-elevated mt-4" style="background: #f8fbff;">
                        <div class="card-body p-4">
                            <h5 class="mb-3">Operating principles</h5>
                            <ul class="mb-0">
                                <li>NovuPay generates and tracks payment requests but does not receive or hold customer funds.</li>
                                <li>Regulated payment processing partners provide QRPh or payment-rail connectivity.</li>
                                <li>Financial institutions perform customer authentication, authorization, and account debiting.</li>
                                <li>Transaction results return to NovuPay for status handling, participant visibility, and reconciliation support.</li>
                            </ul>
                        </div>
                    </div>
                </section>

                <section id="process-flow" class="doc-section">
                    <h2>2. Process flow</h2>
                    <div class="doc-section-kicker">Primary NovuPay lifecycle for formal presentation</div>
                    <div class="flow-sequence">
                        <div class="flow-step-card">
                            <div class="flow-step-no">1</div>
                            <div>
                                <div class="flow-step-title">Payment initiation</div>
                                <p class="flow-step-copy">
                                    A participant or customer starts a payment through a NovuPay-powered digital interface or API submission, providing amount,
                                    reference number, participant identity, customer information, and transaction metadata.
                                </p>
                            </div>
                        </div>
                        <div class="flow-arrow">↓</div>
                        <div class="flow-step-card">
                            <div class="flow-step-no">2</div>
                            <div>
                                <div class="flow-step-title">Transaction creation and validation</div>
                                <p class="flow-step-copy">
                                    NovuPay creates the transaction record, applies platform validation, assigns internal and participant-facing references,
                                    and prepares the request for secure transmission.
                                </p>
                            </div>
                        </div>
                        <div class="flow-arrow">↓</div>
                        <div class="flow-step-card">
                            <div class="flow-step-no">3</div>
                            <div>
                                <div class="flow-step-title">Secure routing to regulated partner</div>
                                <p class="flow-step-copy">
                                    NovuPay routes the payment instruction to an accredited payment processing partner through protected APIs.
                                    The partner provides the regulated connectivity needed for QRPh or other supported payment rails.
                                </p>
                            </div>
                        </div>
                        <div class="flow-arrow">↓</div>
                        <div class="flow-step-card">
                            <div class="flow-step-no">4</div>
                            <div>
                                <div class="flow-step-title">Checkout or QRPh payload generation</div>
                                <p class="flow-step-copy">
                                    The processing partner generates the official payment payload or hosted payment session.
                                    For QRPh transactions, this is the stage where the scannable request is produced for customer use.
                                </p>
                            </div>
                        </div>
                        <div class="flow-arrow">↓</div>
                        <div class="flow-step-card">
                            <div class="flow-step-no">5</div>
                            <div>
                                <div class="flow-step-title">Customer authorization</div>
                                <p class="flow-step-copy">
                                    The customer authorizes payment through a bank or wallet application.
                                    The regulated financial institution performs balance verification, account checks, authentication, and fraud or risk evaluation.
                                </p>
                            </div>
                        </div>
                        <div class="flow-arrow">↓</div>
                        <div class="flow-step-card">
                            <div class="flow-step-no">6</div>
                            <div>
                                <div class="flow-step-title">Response and status handling</div>
                                <p class="flow-step-copy">
                                    Authorization results move back through the payment rail and processing partner to NovuPay.
                                    NovuPay records the resulting status, updates operational records, and preserves the event trail for auditability.
                                </p>
                            </div>
                        </div>
                        <div class="flow-arrow">↓</div>
                        <div class="flow-step-card">
                            <div class="flow-step-no">7</div>
                            <div>
                                <div class="flow-step-title">Participant visibility and reconciliation support</div>
                                <p class="flow-step-copy">
                                    NovuPay returns payment outcomes through redirects, callbacks, APIs, dashboards, or webhooks,
                                    while maintaining transaction metadata, timestamps, and references for reporting, dispute handling, and reconciliation.
                                </p>
                            </div>
                        </div>
                    </div>
                </section>

                <section id="flowchart" class="doc-section">
                    <h2>3. Standard flowchart</h2>
                    <div class="doc-section-kicker">Actor-by-actor transaction flow</div>
                    <div class="card-elevated mb-4">
                        <div class="card-body p-4">
                            <div class="flowchart-figure">
                                <svg class="flowchart-svg" viewBox="0 0 1200 620" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="NovuPay standard transaction flowchart">
                                    <defs>
                                        <linearGradient id="npBox" x1="0%" y1="0%" x2="100%" y2="100%">
                                            <stop offset="0%" stop-color="#0f172a" />
                                            <stop offset="100%" stop-color="#0560aa" />
                                        </linearGradient>
                                        <linearGradient id="partnerBox" x1="0%" y1="0%" x2="100%" y2="100%">
                                            <stop offset="0%" stop-color="#1d4ed8" />
                                            <stop offset="100%" stop-color="#3b82f6" />
                                        </linearGradient>
                                        <linearGradient id="bankBox" x1="0%" y1="0%" x2="100%" y2="100%">
                                            <stop offset="0%" stop-color="#0f766e" />
                                            <stop offset="100%" stop-color="#14b8a6" />
                                        </linearGradient>
                                        <marker id="arrow" markerWidth="10" markerHeight="10" refX="8" refY="5" orient="auto">
                                            <path d="M0,0 L10,5 L0,10 z" fill="#64748b" />
                                        </marker>
                                        <filter id="shadow" x="-20%" y="-20%" width="140%" height="140%">
                                            <feDropShadow dx="0" dy="8" stdDeviation="10" flood-color="#0f172a" flood-opacity="0.14" />
                                        </filter>
                                    </defs>

                                    <rect x="40" y="40" width="170" height="72" rx="18" fill="url(#npBox)" filter="url(#shadow)" />
                                    <text x="125" y="68" text-anchor="middle" font-size="14" font-weight="700" fill="#ffffff">Participant /</text>
                                    <text x="125" y="89" text-anchor="middle" font-size="14" font-weight="700" fill="#ffffff">Customer</text>

                                    <rect x="270" y="40" width="190" height="72" rx="18" fill="url(#npBox)" filter="url(#shadow)" />
                                    <text x="365" y="67" text-anchor="middle" font-size="14" font-weight="700" fill="#ffffff">Payment Initiation</text>
                                    <text x="365" y="88" text-anchor="middle" font-size="12" fill="#dbeafe">Amount, ref, metadata</text>

                                    <rect x="520" y="40" width="200" height="72" rx="18" fill="url(#npBox)" filter="url(#shadow)" />
                                    <text x="620" y="67" text-anchor="middle" font-size="14" font-weight="700" fill="#ffffff">NovuPay</text>
                                    <text x="620" y="88" text-anchor="middle" font-size="12" fill="#dbeafe">Create and validate transaction</text>

                                    <rect x="780" y="40" width="220" height="72" rx="18" fill="url(#partnerBox)" filter="url(#shadow)" />
                                    <text x="890" y="67" text-anchor="middle" font-size="14" font-weight="700" fill="#ffffff">Regulated Processing Partner</text>
                                    <text x="890" y="88" text-anchor="middle" font-size="12" fill="#dbeafe">Route and prepare payment payload</text>

                                    <rect x="1040" y="40" width="120" height="72" rx="18" fill="url(#partnerBox)" filter="url(#shadow)" />
                                    <text x="1100" y="67" text-anchor="middle" font-size="14" font-weight="700" fill="#ffffff">QRPh /</text>
                                    <text x="1100" y="88" text-anchor="middle" font-size="12" fill="#dbeafe">Checkout</text>

                                    <line x1="210" y1="76" x2="270" y2="76" stroke="#64748b" stroke-width="4" marker-end="url(#arrow)" />
                                    <line x1="460" y1="76" x2="520" y2="76" stroke="#64748b" stroke-width="4" marker-end="url(#arrow)" />
                                    <line x1="720" y1="76" x2="780" y2="76" stroke="#64748b" stroke-width="4" marker-end="url(#arrow)" />
                                    <line x1="1000" y1="76" x2="1040" y2="76" stroke="#64748b" stroke-width="4" marker-end="url(#arrow)" />

                                    <rect x="950" y="180" width="210" height="82" rx="18" fill="url(#bankBox)" filter="url(#shadow)" />
                                    <text x="1055" y="210" text-anchor="middle" font-size="14" font-weight="700" fill="#ffffff">Customer Authorizes</text>
                                    <text x="1055" y="231" text-anchor="middle" font-size="12" fill="#ccfbf1">Bank or wallet application</text>
                                    <text x="1055" y="250" text-anchor="middle" font-size="12" fill="#ccfbf1">OTP, balance, fraud checks</text>

                                    <line x1="1100" y1="112" x2="1100" y2="180" stroke="#64748b" stroke-width="4" marker-end="url(#arrow)" />

                                    <rect x="930" y="340" width="230" height="82" rx="18" fill="url(#bankBox)" filter="url(#shadow)" />
                                    <text x="1045" y="370" text-anchor="middle" font-size="14" font-weight="700" fill="#ffffff">Authorization Response</text>
                                    <text x="1045" y="391" text-anchor="middle" font-size="12" fill="#ccfbf1">Approved, declined, or</text>
                                    <text x="1045" y="410" text-anchor="middle" font-size="12" fill="#ccfbf1">further authentication required</text>

                                    <line x1="1055" y1="262" x2="1055" y2="340" stroke="#64748b" stroke-width="4" marker-end="url(#arrow)" />

                                    <rect x="650" y="480" width="240" height="82" rx="18" fill="url(#npBox)" filter="url(#shadow)" />
                                    <text x="770" y="510" text-anchor="middle" font-size="14" font-weight="700" fill="#ffffff">NovuPay Status Handling</text>
                                    <text x="770" y="531" text-anchor="middle" font-size="12" fill="#dbeafe">Record state, timestamps,</text>
                                    <text x="770" y="550" text-anchor="middle" font-size="12" fill="#dbeafe">references, and event trail</text>

                                    <rect x="330" y="480" width="240" height="82" rx="18" fill="url(#npBox)" filter="url(#shadow)" />
                                    <text x="450" y="510" text-anchor="middle" font-size="14" font-weight="700" fill="#ffffff">Participant Visibility</text>
                                    <text x="450" y="531" text-anchor="middle" font-size="12" fill="#dbeafe">API response, redirect,</text>
                                    <text x="450" y="550" text-anchor="middle" font-size="12" fill="#dbeafe">callback, dashboard, webhook</text>

                                    <rect x="40" y="480" width="220" height="82" rx="18" fill="url(#npBox)" filter="url(#shadow)" />
                                    <text x="150" y="510" text-anchor="middle" font-size="14" font-weight="700" fill="#ffffff">Reporting and</text>
                                    <text x="150" y="531" text-anchor="middle" font-size="14" font-weight="700" fill="#ffffff">Reconciliation Support</text>
                                    <text x="150" y="550" text-anchor="middle" font-size="12" fill="#dbeafe">Operational records only</text>

                                    <path d="M930 381 C880 381, 870 521, 890 521" fill="none" stroke="#64748b" stroke-width="4" marker-end="url(#arrow)" />
                                    <line x1="650" y1="521" x2="570" y2="521" stroke="#64748b" stroke-width="4" marker-end="url(#arrow)" />
                                    <line x1="330" y1="521" x2="260" y2="521" stroke="#64748b" stroke-width="4" marker-end="url(#arrow)" />

                                    <rect x="32" y="590" width="1136" height="18" rx="9" fill="#dbeafe" />
                                    <text x="600" y="604" text-anchor="middle" font-size="11" font-weight="700" fill="#1e3a8a" letter-spacing="1">NOVUPAY ORCHESTRATION LAYER: INITIATION, ROUTING, STATUS HANDLING, VISIBILITY, REPORTING</text>

                                    <rect x="930" y="150" width="240" height="290" rx="24" fill="none" stroke="#14b8a6" stroke-dasharray="10 8" stroke-width="3" />
                                    <text x="1050" y="145" text-anchor="middle" font-size="12" font-weight="700" fill="#0f766e" letter-spacing="1">REGULATED FINANCIAL PROCESSING ZONE</text>
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div class="card-elevated">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="standards-table">
                                    <thead>
                                        <tr>
                                            <th style="width: 12%;">Stage</th>
                                            <th style="width: 20%;">Primary actor</th>
                                            <th>Standard action</th>
                                            <th style="width: 26%;">Primary output</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>01</td>
                                            <td>Participant / Customer</td>
                                            <td>Initiates payment from a NovuPay interface or integrated participant system.</td>
                                            <td>Payment request with amount, reference, and metadata.</td>
                                        </tr>
                                        <tr>
                                            <td>02</td>
                                            <td>NovuPay</td>
                                            <td>Creates the transaction, validates data, assigns references, and prepares the orchestration payload.</td>
                                            <td>Registered transaction record ready for routing.</td>
                                        </tr>
                                        <tr>
                                            <td>03</td>
                                            <td>NovuPay</td>
                                            <td>Routes the request to a regulated payment processing partner through secure API communication.</td>
                                            <td>Partner-bound payment instruction.</td>
                                        </tr>
                                        <tr>
                                            <td>04</td>
                                            <td>Processing partner</td>
                                            <td>Generates the QRPh or hosted payment payload and returns the payment experience to the orchestration layer.</td>
                                            <td>QR code, redirect URL, or hosted checkout session.</td>
                                        </tr>
                                        <tr>
                                            <td>05</td>
                                            <td>Customer + Financial institution</td>
                                            <td>Customer authorizes payment using a participating bank or wallet, while the institution performs regulated authentication and authorization controls.</td>
                                            <td>Approved, declined, or further-authentication result.</td>
                                        </tr>
                                        <tr>
                                            <td>06</td>
                                            <td>Payment rail / Partner</td>
                                            <td>Financial result traverses regulated infrastructure and returns operational status to NovuPay through the connected partner.</td>
                                            <td>Transaction confirmation message.</td>
                                        </tr>
                                        <tr>
                                            <td>07</td>
                                            <td>NovuPay</td>
                                            <td>Updates transaction state, records references and timestamps, and triggers participant-facing response channels.</td>
                                            <td>Updated status, webhook event, callback, redirect, and reporting entry.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="callout-band mt-4">
                        <p style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.08em; opacity: 1; margin-bottom: 8px; color: #fff;">Settlement boundary</p>
                        <p style="font-size: 15px; line-height: 1.75; color: #fff;">
                            Authorization, clearing, and settlement occur within regulated financial institutions, payment rails, and processing partners.
                            NovuPay remains the transaction orchestration and visibility layer, not the custodian of funds.
                        </p>
                    </div>
                </section>

                <section id="steps" class="doc-section">
                    <h2>4. Step-by-step standard model</h2>
                    <div class="doc-section-kicker">Detailed lifecycle explanation</div>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="card-elevated h-100">
                                <div class="card-body p-4">
                                    <h5 class="mb-3">Step 1. Request capture</h5>
                                    <p class="mb-0">
                                        Payment capture begins from checkout pages, participant applications, QR-oriented interfaces, or API-based initiation.
                                        Standard inputs include amount, reference number, participant identity, and related metadata.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card-elevated h-100">
                                <div class="card-body p-4">
                                    <h5 class="mb-3">Step 2. Orchestration setup</h5>
                                    <p class="mb-0">
                                        NovuPay persists the request, structures the data, and prepares routing instructions so downstream partners receive a complete and traceable payment message.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card-elevated h-100">
                                <div class="card-body p-4">
                                    <h5 class="mb-3">Step 3. Partner-side payment enablement</h5>
                                    <p class="mb-0">
                                        The regulated partner generates the QRPh payload or equivalent payment session and enables connectivity to the appropriate national or partner-operated payment rail.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card-elevated h-100">
                                <div class="card-body p-4">
                                    <h5 class="mb-3">Step 4. Customer approval event</h5>
                                    <p class="mb-0">
                                        The payer authorizes the transaction using a banking app or electronic wallet, and the regulated institution executes the authentication and account-level checks required for approval.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card-elevated h-100">
                                <div class="card-body p-4">
                                    <h5 class="mb-3">Step 5. Status return path</h5>
                                    <p class="mb-0">
                                        Approved, declined, or other operational responses travel back through the partner integration path and are recorded by NovuPay as the authoritative orchestration status.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card-elevated h-100">
                                <div class="card-body p-4">
                                    <h5 class="mb-3">Step 6. Operational visibility</h5>
                                    <p class="mb-0">
                                        Participants receive transaction visibility through dashboards, redirects, callbacks, status checks, or webhook notifications,
                                        enabling upstream systems to synchronize their records.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section id="roles" class="doc-section">
                    <h2>5. Roles and boundaries</h2>
                    <div class="doc-section-kicker">Who does what in the NovuPay model</div>
                    <div class="boundary-grid">
                        <div class="card-elevated h-100">
                            <div class="card-body p-4">
                                <h5 class="mb-3">What NovuPay does</h5>
                                <ul class="mb-0">
                                    <li>Generates payment requests and transaction references.</li>
                                    <li>Routes payment instructions to regulated partners.</li>
                                    <li>Handles redirects, callbacks, status checks, and webhooks.</li>
                                    <li>Maintains transaction logs, audit trails, and reporting records.</li>
                                </ul>
                            </div>
                        </div>
                        <div class="card-elevated h-100">
                            <div class="card-body p-4">
                                <h5 class="mb-3">What NovuPay does not do</h5>
                                <ul class="mb-0">
                                    <li>Hold deposits or settlement balances.</li>
                                    <li>Perform interbank clearing or fund transfers.</li>
                                    <li>Act as the financial institution of the payer or beneficiary.</li>
                                    <li>Assume custody of customer funds at any stage of the process.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="role-grid mt-4">
                        <div class="card-elevated h-100">
                            <div class="card-body p-4">
                                <h5 class="mb-3">Platform participants</h5>
                                <p class="mb-0">
                                    Merchants, utilities, government entities, and integrated services use NovuPay to initiate transactions, monitor payment outcomes, and support their operational reporting.
                                </p>
                            </div>
                        </div>
                        <div class="card-elevated h-100">
                            <div class="card-body p-4">
                                <h5 class="mb-3">Processing partners and institutions</h5>
                                <p class="mb-0">
                                    Payment processing partners provide rail connectivity, while financial institutions execute authentication, authorization, debiting, crediting, and regulated settlement activities.
                                </p>
                            </div>
                        </div>
                    </div>
                </section>

                <section id="architecture" class="doc-section">
                    <h2>6. Architecture alignment</h2>
                    <div class="doc-section-kicker">How the process flow maps to the platform</div>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="card-elevated h-100">
                                <div class="card-body p-4">
                                    <h5 class="mb-3">Frontend and participant interfaces</h5>
                                    <p class="mb-0">
                                        Web-based checkout pages, participant dashboards, and supporting documentation provide the entry point for payment initiation and transaction visibility.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card-elevated h-100">
                                <div class="card-body p-4">
                                    <h5 class="mb-3">Backend orchestration services</h5>
                                    <p class="mb-0">
                                        Modular backend services create payment requests, route transactions, receive responses, process webhooks, and maintain synchronized transaction state.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card-elevated h-100">
                                <div class="card-body p-4">
                                    <h5 class="mb-3">Operational data storage</h5>
                                    <p class="mb-0">
                                        Transaction records, participant payloads, references, timestamps, audit logs, and compliance-relevant operational data are stored to support traceability and reporting.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card-elevated h-100">
                                <div class="card-body p-4">
                                    <h5 class="mb-3">Infrastructure posture</h5>
                                    <p class="mb-0">
                                        The platform is positioned for encrypted communications, scalable routing, redundancy, continuous monitoring, and secure integration with external payment infrastructure.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section id="controls" class="doc-section">
                    <h2>7. Controls and resilience</h2>
                    <div class="doc-section-kicker">Security, risk, and continuity controls around the flow</div>
                    <div class="control-grid">
                        <div class="card-elevated h-100">
                            <div class="card-body p-4">
                                <h5 class="mb-3">Security controls</h5>
                                <ul class="mb-0">
                                    <li>Role-based access control and protected operational access.</li>
                                    <li>Secure API authentication for partner and participant connectivity.</li>
                                    <li>Encrypted transport and protected storage of operational data.</li>
                                    <li>Comprehensive logging of status changes, callbacks, and webhooks.</li>
                                </ul>
                            </div>
                        </div>
                        <div class="card-elevated h-100">
                            <div class="card-body p-4">
                                <h5 class="mb-3">Risk and resilience measures</h5>
                                <ul class="mb-0">
                                    <li>Continuous monitoring of integrations, platform performance, and security events.</li>
                                    <li>Incident classification, containment, recovery, and post-incident review procedures.</li>
                                    <li>Redundancy, backup infrastructure, and recovery readiness.</li>
                                    <li>Ongoing due diligence and oversight for third-party payment partners.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </section>

                <section id="summary" class="doc-section">
                    <h2>8. Summary</h2>
                    <div class="doc-section-kicker">Standard position of the NovuPay OPS model</div>
                    <div class="card-elevated mt-4">
                        <div class="card-body p-4 p-md-5">
                            <h5 class="mb-3">NovuPay standard process flow, in summary</h5>
                            <ul class="mb-4">
                                <li>Initiates and structures payment transactions.</li>
                                <li>Routes requests to regulated payment processing partners.</li>
                                <li>Supports QRPh or hosted payment enablement for customer authorization.</li>
                                <li>Records transaction outcomes and distributes status updates through multiple visibility channels.</li>
                                <li>Supports reconciliation, auditability, and operational reporting without performing settlement or fund custody.</li>
                            </ul>
                            <div class="process-actions">
                                <a href="{{ route('services.novupay.whitepaper') }}" class="btn btn-primary" style="padding: 10px 24px;">Read the OPS white paper</a>
                                <a href="{{ route('contact') }}" class="btn btn-outline-primary" style="padding: 10px 24px;">Contact our team</a>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>
@endsection
