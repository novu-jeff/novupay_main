@extends('layouts.app')

@section('title', 'NovuPay OPS White Paper | NovuPay')
@section('meta_description', 'NovuPay is a digital payment gateway: QR Ph payment requests through a partner financial institution, status recording, merchant notification, and reconciliation reporting. The partner financial institution holds and settles the funds. NovuPay does not.')

@section('base')
<div class="page-section page-section--light">
    <div class="container page-section-inner" style="max-width: 1160px;">
        <div class="section-heading" style="margin-bottom: 28px;">
            <div class="section-kicker">NovuPay white paper</div>
            <div class="section-title">Digital payment gateway for QR Ph payments</div>
            <div class="section-subtitle" style="max-width: 820px;">
                This white paper describes NovuPay as a digital payment gateway: QR Ph payment requests, partner postbacks, merchant notification, and reconciliation reporting. The partner financial institution holds and settles the funds. NovuPay does not.
            </div>
            <div class="mt-4">
                <a href="{{ route('services.novupay.whitepaper.docx') }}" class="btn btn-primary" style="padding: 10px 24px;">Download DOCX</a>
            </div>
        </div>

        <div class="card-elevated mb-5" style="border-radius: 20px; overflow: hidden;">
            <div class="card-body p-4 p-md-5" style="background: linear-gradient(135deg, #0f172a 0%, #0560aa 58%, #dbeafe 180%); color: #fff;">
                <div class="row g-4 align-items-center">
                    <div class="col-lg-8">
                        <div class="pill mb-3" style="background: rgba(255,255,255,0.14); color: #fff;">OPS documentation for partners, participants, and reviewers</div>
                        <h1 class="whitepaper-hero-title" style="font-size: 40px; font-weight: 700; line-height: 1.15; margin-bottom: 16px;">
                            A digital payment gateway for QR Ph initiation, status recording, and reporting
                        </h1>
                        <p style="font-size: 16px; line-height: 1.8; color: rgba(255,255,255,0.88); margin-bottom: 0;">
                            NovuPay is a digital payment gateway. It creates payment requests, shows a QR Ph checkout, routes the QR Ph instruction to the partner financial institution, receives that institution’s postback, records status, notifies the merchant, and supports reconciliation. The partner financial institution receives, holds, and settles the funds to the merchant’s bank. NovuPay does not accept deposits, hold customer or merchant funds, operate a stored-value or e-money wallet, pay out balances, or perform clearing and settlement.
                        </p>
                    </div>
                    <div class="col-lg-4">
                        <div class="whitepaper-hero-stats" style="display: grid; gap: 12px;">
                            <div style="padding: 16px 18px; border-radius: 16px; background: rgba(15,23,42,0.34); border: 1px solid rgba(255,255,255,0.18);">
                                <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.08em; opacity: 0.75;">NovuPay</div>
                                <div style="font-size: 18px; font-weight: 700; line-height: 1.3;">Digital payment gateway</div>
                            </div>
                            <div style="padding: 16px 18px; border-radius: 16px; background: rgba(15,23,42,0.34); border: 1px solid rgba(255,255,255,0.18);">
                                <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.08em; opacity: 0.75;">Live payment channel</div>
                                <div style="font-size: 20px; font-weight: 700;">QR Ph</div>
                            </div>
                            <div style="padding: 16px 18px; border-radius: 16px; background: rgba(15,23,42,0.34); border: 1px solid rgba(255,255,255,0.18);">
                                <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.08em; opacity: 0.75;">Funds custody</div>
                                <div style="font-size: 20px; font-weight: 700;">Not held by NovuPay</div>
                            </div>
                            <div style="padding: 16px 18px; border-radius: 16px; background: rgba(15,23,42,0.34); border: 1px solid rgba(255,255,255,0.18);">
                                <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.08em; opacity: 0.75;">Settlement</div>
                                <div style="font-size: 18px; font-weight: 700; line-height: 1.3;">Partner financial institution</div>
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
                    <li><a href="#overview">Gateway overview</a></li>
                    <li><a href="#participants">Payment system participants</a></li>
                    <li><a href="#lifecycle">Transaction lifecycle</a></li>
                    <li><a href="#routing">Payment routing and funds flow</a></li>
                    <li><a href="#settlement">Settlement and clearing framework</a></li>
                    <li><a href="#partners">Integration with regulated partners</a></li>
                    <li><a href="#architecture">NovuPay system architecture</a></li>
                    <li><a href="#security">Gateway security controls</a></li>
                    <li><a href="#risk">Risk management framework</a></li>
                    <li><a href="#resilience">Operational resilience</a></li>
                    <li><a href="#summary">Gateway role summary</a></li>
                </ul>
            </aside>

            <div class="doc-content">
                <section id="overview" class="doc-section">
                    <h2>1. Gateway overview</h2>
                    <div class="doc-section-kicker">Digital payment gateway in the payment ecosystem</div>
                    <p>
                        NovuPay is a digital payment gateway in the Philippine payments ecosystem. QR Ph is the national retail payment rail. The only live NovuPay payment channel is QR Ph, through a partner financial institution. GCash, Maya, and bank apps are the buyer’s QR Ph scanner. They are not NovuPay channels.
                    </p>
                    <p>
                        Other payment channels are a possible addition in the future, including online banking, OTC, installment platforms such as BillEase, and card payments. They are not live. If they are enabled later, the partner financial institution would still receive, hold, and settle the funds. NovuPay would not.
                    </p>
                    <p>
                        NovuPay creates payment requests, shows a QR Ph checkout, routes the QR Ph instruction to the partner financial institution, receives that institution’s postback, records status, notifies the merchant, and supports reconciliation. A Paid status means NovuPay received a successful postback from the partner financial institution. It is not a guarantee that the institution has settled to the merchant’s bank.
                    </p>
                    <p>
                        The partner financial institution receives, holds, and settles the funds to the merchant’s bank. NovuPay does not accept deposits, hold customer or merchant funds, operate a stored-value or e-money wallet, pay out balances, or perform clearing and settlement.
                    </p>
                    <div class="card-elevated mt-4" style="background: #f8fbff;">
                        <div class="card-body p-4">
                            <h5 class="mb-3">Core gateway functions</h5>
                            <ul class="mb-0">
                                <li>Creation of a payment request with amount, currency, reference, customer details, and callback.</li>
                                <li>Presentation of a QR Ph checkout and routing of that QR Ph instruction to the partner financial institution.</li>
                                <li>Acceptance of a partner financial institution postback only after source, signature, amount, and expiry checks, then recording of Paid.</li>
                                <li>Notification of the merchant through the dashboard, the status API, and the merchant webhook.</li>
                                <li>Reconciliation that matches NovuPay’s Paid record to the partner financial institution’s settlement report.</li>
                            </ul>
                        </div>
                    </div>
                </section>

                <section id="participants" class="doc-section">
                    <h2>2. Payment system participants</h2>
                    <div class="doc-section-kicker">Distinct roles in transaction processing</div>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="card-elevated h-100">
                                <div class="card-body p-4">
                                    <h5 class="mb-3">Buyer</h5>
                                    <p class="mb-0">
                                        The buyer pays by scanning the NovuPay QR Ph in their own QR Ph-capable app, such as GCash, Maya, or a QR Ph bank app. Those apps are the buyer’s scanner. Funds never sit with NovuPay.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card-elevated h-100">
                                <div class="card-body p-4">
                                    <h5 class="mb-3">Merchant (participant)</h5>
                                    <p class="mb-0">
                                        The merchant creates payment requests, receives status through the dashboard, status API, and merchant webhook, and is settled by the partner financial institution to a designated bank account.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card-elevated h-100">
                                <div class="card-body p-4">
                                    <h5 class="mb-3">Partner financial institution</h5>
                                    <p class="mb-0">
                                        The partner financial institution is the acquiring and settlement partner. It receives, holds, and settles the funds to the merchant’s bank. Settlement timing, bank credit, and acquiring decisions belong to the partner financial institution and QR Ph rules.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card-elevated h-100">
                                <div class="card-body p-4">
                                    <h5 class="mb-3">QR Ph participants and the buyer’s bank or wallet</h5>
                                    <p class="mb-0">
                                        QR Ph participants and the buyer’s bank or wallet authorize and move funds on the QR Ph rail. NovuPay does not authorize the debit and does not perform clearing and settlement.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section id="lifecycle" class="doc-section">
                    <h2>3. Payment transaction lifecycle</h2>
                    <div class="doc-section-kicker">From initiation to transaction status confirmation</div>
                    <p>
                        Each live payment follows one path. The merchant or API creates a request, NovuPay asks the partner financial institution for a QR Ph, the buyer pays in their own app, the partner financial institution posts the result, NovuPay records Paid and notifies the merchant, and the partner financial institution later settles to the merchant’s bank. NovuPay’s later reconciliation matches its Paid record to that settlement report.
                    </p>
                    <div class="card-elevated" style="background: #f8fbff;">
                        <div class="card-body p-4 p-md-5">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <h5 class="mb-3">Step 1. Payment request</h5>
                                    <p>
                                        The merchant or API creates a payment request. The request carries the amount, currency, reference, customer details, and callback.
                                    </p>
                                    <h5 class="mb-3">Step 2. QR Ph generation</h5>
                                    <p>
                                        NovuPay persists the request and calls the partner financial institution to generate the QR Ph. NovuPay then shows that QR Ph checkout.
                                    </p>
                                    <h5 class="mb-3">Step 3. Buyer payment</h5>
                                    <p class="mb-0">
                                        The buyer pays by scanning that QR Ph in their own bank or wallet app. GCash, Maya, and QR Ph bank apps are scanners for this step. They are not NovuPay channels.
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <h5 class="mb-3">Step 4. Partner financial institution postback</h5>
                                    <p>
                                        The partner financial institution posts the result to NovuPay. NovuPay accepts the postback only after source, signature, amount, and expiry checks, then marks the payment Paid. Paid means that postback succeeded. It does not mean the partner financial institution has settled to the merchant’s bank.
                                    </p>
                                    <h5 class="mb-3">Step 5. Merchant notification</h5>
                                    <p>
                                        NovuPay notifies the merchant through the dashboard, the status API, and the merchant webhook.
                                    </p>
                                    <h5 class="mb-3">Step 6. Partner settlement and NovuPay reconciliation</h5>
                                    <p class="mb-0">
                                        The partner financial institution settles to the merchant’s bank on its own cycle. NovuPay reconciliation later matches its Paid record to that institution’s settlement report. That match is reporting. NovuPay does not move the funds.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section id="routing" class="doc-section">
                    <h2>4. Payment routing and funds flow</h2>
                    <div class="doc-section-kicker">The gateway does not move the funds</div>
                    <p>
                        NovuPay routes the QR Ph instruction to the partner financial institution and records what that institution reports back. It does not sit on the path of the funds. The buyer pays from their own app, the partner financial institution receives and holds the funds, and the partner financial institution settles them to the merchant’s bank.
                    </p>
                    <p>
                        NovuPay sits beside that path. It generates the QR Ph, validates the postback, marks the transaction Paid, notifies the merchant, and later matches the record to the partner financial institution’s report.
                    </p>
                    <div class="card-elevated mt-4">
                        <div class="card-body p-4">
                            <h5 class="mb-3">Funds flow</h5>
                            <p class="mb-2">Buyer</p>
                            <p class="mb-2">→ Buyer’s QR Ph app (GCash, Maya, or a QR Ph bank app)</p>
                            <p class="mb-2">→ Partner financial institution QR Ph</p>
                            <p class="mb-2">→ Partner financial institution holds the funds</p>
                            <p class="mb-3">→ Partner financial institution settles to the merchant’s bank</p>
                            <p class="mb-0">
                                NovuPay does not accept the deposit, hold the funds, or settle them. Settlement timing, bank credit, and acquiring decisions belong to the partner financial institution and QR Ph rules. NovuPay does not control them.
                            </p>
                        </div>
                    </div>
                </section>

                <section id="settlement" class="doc-section">
                    <h2>5. Settlement and clearing framework</h2>
                    <div class="doc-section-kicker">Regulated infrastructure performs financial settlement</div>
                    <p>
                        The partner financial institution holds the funds and settles them to the merchant’s bank. Clearing and settlement are not performed by NovuPay. Settlement timing, bank credit, and acquiring decisions belong to the partner financial institution and QR Ph rules. NovuPay does not control them.
                    </p>
                    <p>
                        A Paid status means NovuPay received a successful postback from the partner financial institution. It is not a guarantee that the partner financial institution has settled to the merchant’s bank. Reconciliation matches NovuPay’s Paid record to the partner financial institution’s settlement report. It is reporting, not NovuPay moving money.
                    </p>
                    <p>
                        NovuPay may collect the merchant’s settlement bank details and pass them to the partner financial institution. NovuPay does not use those details to pay the merchant. NovuPay does not accept deposits, hold customer or merchant funds, pay out balances, or keep a merchant balance.
                    </p>
                </section>

                <section id="partners" class="doc-section">
                    <h2>6. Integration with regulated payment processing partners</h2>
                    <div class="doc-section-kicker">Connectivity to external payment infrastructure</div>
                    <p>
                        The partner financial institution is NovuPay’s acquiring and settlement partner on QR Ph, the national retail payment rail. NovuPay sends the payment request so the partner financial institution can generate the QR Ph, then receives the postback and records the result. The partner financial institution receives, holds, and settles the funds. NovuPay does not become the custodian of those funds.
                    </p>
                    <p>
                        The only live NovuPay payment channel is QR Ph, through the partner financial institution. Other payment channels are a possible addition in the future: online banking, OTC, installment platforms such as BillEase, and card payments. They are not live until they are enabled. Enabling a future channel would not move custody or settlement to NovuPay.
                    </p>
                </section>

                <section id="architecture" class="doc-section">
                    <h2>7. NovuPay system architecture</h2>
                    <div class="doc-section-kicker">Application and service structure</div>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="card-elevated h-100">
                                <div class="card-body p-4">
                                    <h5 class="mb-3">Checkout and participant interfaces</h5>
                                    <p class="mb-0">
                                        NovuPay shows the buyer a QR Ph checkout and gives the merchant a console for payment requests, status, and reports. Operator access is a separate console. Initiation is also available through the merchant API.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card-elevated h-100">
                                <div class="card-body p-4">
                                    <h5 class="mb-3">QR Ph gateway services</h5>
                                    <p class="mb-0">
                                        Gateway services persist the payment request, call the partner financial institution to generate the QR Ph, check the postback, mark the payment Paid, and notify the merchant. They do not hold or settle funds.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card-elevated h-100">
                                <div class="card-body p-4">
                                    <h5 class="mb-3">Operational records</h5>
                                    <p class="mb-0">
                                        NovuPay stores the payment request, status, references, and timestamps needed to notify the merchant, support audit, and match a Paid record to the partner financial institution’s settlement report.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card-elevated h-100">
                                <div class="card-body p-4">
                                    <h5 class="mb-3">Partner financial institution boundary</h5>
                                    <p class="mb-0">
                                        The partner financial institution is the boundary where funds are received, held, and settled to the merchant’s bank. NovuPay exchanges the QR Ph instruction and the postback with that institution, and remains outside the funds path.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section id="security" class="doc-section">
                    <h2>8. Gateway security controls</h2>
                    <div class="doc-section-kicker">System integrity and access protection</div>
                    <p>
                        Merchant API access uses server-side credentials: an API key and a client id. The client secret is for webhook verification and must stay on the merchant server. It is not a client-side credential.
                    </p>
                    <p>
                        Postbacks from the partner financial institution are checked for allowed source, signature, amount, and expiry before NovuPay changes status. Transport is HTTPS. Transaction and webhook activity is logged for audit and reconciliation. Role-based access separates the merchant console from the operator console.
                    </p>
                    <ul>
                        <li>Merchant API access uses an API key and a client id on the merchant server.</li>
                        <li>The client secret verifies merchant webhooks and stays on the merchant server.</li>
                        <li>Partner financial institution postbacks are checked for allowed source, signature, amount, and expiry before status changes.</li>
                        <li>Transport is HTTPS. Transaction and webhook activity is logged for audit and reconciliation.</li>
                        <li>Role-based access separates merchant and operator consoles.</li>
                    </ul>
                </section>

                <section id="risk" class="doc-section">
                    <h2>9. Payment system risk management framework</h2>
                    <div class="doc-section-kicker">Operational, security, and integration risk controls</div>
                    <p>
                        NovuPay’s risk posture as a digital payment gateway rests on status integrity, partner boundaries, and traceable records. Relevant categories are operational risk, cybersecurity risk, availability of the QR Ph instruction path, postback integrity, and third-party dependence on the partner financial institution and the QR Ph rail. NovuPay does not manage a merchant balance, and it does not treat a Paid status as completed settlement.
                    </p>
                    <div class="card-elevated mt-4" style="background: #f8fbff;">
                        <div class="card-body p-4">
                            <h5 class="mb-3">Risk mitigation approach</h5>
                            <ul class="mb-0">
                                <li>Accept a partner financial institution postback, and mark Paid, only after source, signature, amount, and expiry checks.</li>
                                <li>Keep Paid separate from settlement. Paid records the postback. Bank credit remains with the partner financial institution.</li>
                                <li>Keep the gateway separate from custody. NovuPay does not hold funds or settle them.</li>
                                <li>Retain transaction and webhook logs for audit, reconciliation, and post-incident review.</li>
                                <li>Treat the partner financial institution and QR Ph rules as the source of settlement timing, bank credit, and acquiring decisions.</li>
                            </ul>
                        </div>
                    </div>
                </section>

                <section id="resilience" class="doc-section">
                    <h2>10. Operational resilience and incident response</h2>
                    <div class="doc-section-kicker">Continuity during disruptions</div>
                    <p>
                        QR Ph operations depend on NovuPay’s ability to create a payment request, obtain a QR Ph from the partner financial institution, receive a postback, notify the merchant, and later match Paid records to that institution’s settlement report. NovuPay does not mark a payment Paid without a postback that passes the required checks, and it does not hold or advance funds when the partner financial institution or QR Ph is unavailable.
                    </p>
                    <p>
                        When an incident occurs, the response is to identify the event, classify its severity, contain it, restore the affected request, postback, or notification path, investigate the cause, and document the incident. A partner outage remains with the partner financial institution or the QR Ph rail to resolve for acquiring and settlement. NovuPay’s continuity task is to restore QR Ph checkout, status recording, merchant notification, and reconciliation reporting.
                    </p>
                </section>

                <section id="summary" class="doc-section">
                    <h2>11. Gateway role summary</h2>
                    <div class="doc-section-kicker">Digital payment gateway in one view</div>
                    <p>
                        NovuPay is a digital payment gateway. It generates and routes the QR Ph, records status from the partner financial institution, and reports. The partner financial institution holds and settles. NovuPay does not. Other payment channels — online banking, OTC, installment platforms such as BillEase, and card payments — are a possible addition in the future and are not live.
                    </p>
                    <div class="card-elevated mt-4">
                        <div class="card-body p-4 p-md-5">
                            <h5 class="mb-3">NovuPay, in summary</h5>
                            <ul class="mb-4">
                                <li>Operates as a digital payment gateway for QR Ph.</li>
                                <li>Generates the QR Ph and routes the QR Ph instruction to the partner financial institution.</li>
                                <li>Records Paid when a successful partner financial institution postback passes source, signature, amount, and expiry checks.</li>
                                <li>Notifies the merchant and matches Paid records to the partner financial institution’s settlement report.</li>
                                <li>Leaves holding and settlement with the partner financial institution, including timing, bank credit, and acquiring decisions.</li>
                                <li>Does not accept deposits, hold funds, operate a stored-value or e-money wallet, pay out balances, or perform clearing and settlement.</li>
                                <li>May add other channels in the future, including online banking, OTC, installment platforms such as BillEase, and card payments. Those channels are not live.</li>
                            </ul>
                            <div class="whitepaper-actions">
                                <a href="{{ route('services.novupay.whitepaper.docx') }}" class="btn btn-outline-primary me-2 mb-2" style="padding: 10px 24px;">Download DOCX</a>
                                <a href="{{ route('contact') }}" class="btn btn-primary me-2 mb-2" style="padding: 10px 24px;">Contact our team</a>
                                <a href="{{ route('services.novupay.processflow') }}" class="btn btn-outline-primary me-2 mb-2" style="padding: 10px 24px;">View process flow</a>
                                <a href="{{ route('api.docs') }}" class="btn btn-outline-primary mb-2" style="padding: 10px 24px;">View API docs</a>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>
@endsection
