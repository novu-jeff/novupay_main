@extends('layouts.app')

@section('title', 'NovuSurge White Paper for Electric Utilities | NovuPay')
@section('meta_description', 'Read the NovuSurge white paper for electric and power utilities: an independent SaaS platform for offline-first meter reading, traceable power billing, customer self-service, reporting, and payment-agnostic collections.')

@section('base')
<div class="page-section page-section--light">
    <div class="container page-section-inner" style="max-width: 1160px;">
        <div class="section-heading" style="margin-bottom: 28px;">
            <div class="section-kicker">NovuSurge white paper</div>
            <div class="section-title">An independent SaaS platform for modern electric utility operations</div>
            <div class="section-subtitle" style="max-width: 760px;">
                NovuSurge is a standalone software platform for power billing, electric meter reading operations, customer service, reporting, and collections.
                It is now incorporated under NovuPay, and our team continues to maintain and develop it, but the product itself remains payment-agnostic and does not depend on NovuPay to operate.
            </div>
        </div>

        <div class="card-elevated mb-5" style="border-radius: 20px; overflow: hidden;">
            <div class="card-body p-4 p-md-5" style="background: linear-gradient(135deg, #0f172a 0%, #6b1f00 55%, #fee2d3 180%); color: #fff;">
                <div class="row g-4 align-items-center">
                    <div class="col-lg-8">
                        <div class="pill mb-3" style="background: rgba(255,255,255,0.14); color: #fff;">For electric cooperatives, distribution utilities, and collection-driven operations</div>
                        <h1 class="whitepaper-hero-title" style="font-size: 40px; font-weight: 700; line-height: 1.15; margin-bottom: 16px;">
                            From electric meter reading to billing, collections, and reporting in one connected system
                        </h1>
                        <p style="font-size: 16px; line-height: 1.8; color: rgba(255,255,255,0.88); margin-bottom: 0;">
                            NovuSurge is built for organizations that need reliable meter reading execution, accurate power billing, auditable account histories,
                            customer-facing account access, and flexible collection workflows. It supports cashier-driven operations, online payment integrations,
                            offline capture, and detailed reporting without forcing the organization into a single payment provider or operating model.
                        </p>
                    </div>
                    <div class="col-lg-4">
                        <div class="whitepaper-hero-stats" style="display: grid; gap: 12px;">
                            <div style="padding: 16px 18px; border-radius: 16px; background: rgba(15,23,42,0.34); border: 1px solid rgba(255,255,255,0.18);">
                                <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.08em; opacity: 0.75;">Deployment model</div>
                                <div style="font-size: 20px; font-weight: 700;">Independent SaaS</div>
                            </div>
                            <div style="padding: 16px 18px; border-radius: 16px; background: rgba(15,23,42,0.34); border: 1px solid rgba(255,255,255,0.18);">
                                <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.08em; opacity: 0.75;">Field model</div>
                                <div style="font-size: 20px; font-weight: 700;">Offline-first</div>
                            </div>
                            <div style="padding: 16px 18px; border-radius: 16px; background: rgba(15,23,42,0.34); border: 1px solid rgba(255,255,255,0.18);">
                                <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.08em; opacity: 0.75;">Billing model</div>
                                <div style="font-size: 20px; font-weight: 700;">One reading, one bill</div>
                            </div>
                            <div style="padding: 16px 18px; border-radius: 16px; background: rgba(15,23,42,0.34); border: 1px solid rgba(255,255,255,0.18);">
                                <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.08em; opacity: 0.75;">Collections model</div>
                                <div style="font-size: 20px; font-weight: 700;">Payment-agnostic</div>
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
                    <li><a href="#executive-summary">Executive summary</a></li>
                    <li><a href="#positioning">Product positioning</a></li>
                    <li><a href="#challenge">Operational challenge</a></li>
                    <li><a href="#platform">What NovuSurge includes</a></li>
                    <li><a href="#architecture">Platform architecture</a></li>
                    <li><a href="#workflow">Core workflow</a></li>
                    <li><a href="#payments">Collections and payment flexibility</a></li>
                    <li><a href="#governance">Data, controls, and auditability</a></li>
                    <li><a href="#benefits">Operational benefits</a></li>
                    <li><a href="#implementation">Implementation model</a></li>
                    <li><a href="#who-its-for">Best-fit organizations</a></li>
                </ul>
            </aside>

            <div class="doc-content">
                <section id="executive-summary" class="doc-section">
                    <h2>Executive summary</h2>
                    <div class="doc-section-kicker">Why this matters</div>
                    <p>
                        Many electric utility teams still run on fragmented processes: meter reading activity in one workflow, billing in another,
                        cashier collections in another, spreadsheets for ageing and reporting, and manual follow-up for customer concerns. The result is operational
                        drag, weak visibility, and a higher risk of reconciliation errors.
                    </p>
                    <p>
                        NovuSurge was built to solve that operational fragmentation. It provides one connected operating model for accounts, meter reading activity,
                        billing rules, ledgers, reports, customer-facing account views, and collections. The outcome is not just digitization, but a cleaner and
                        more accountable path from service activity to bill generation to payment posting.
                    </p>
                </section>

                <section id="positioning" class="doc-section">
                    <h2>Product positioning</h2>
                    <div class="doc-section-kicker">Independent SaaS for electric utilities, maintained by the NovuPay team</div>
                    <p>
                        NovuSurge is an independent SaaS product. It is not merely a payment page, plugin, or billing add-on. It is a full operational platform
                        intended for organizations that need structured meter reading execution, account servicing, billing computation, reporting, and collection support in one system.
                    </p>
                    <p>
                        NovuPay has incorporated NovuSurge and our development team continues to maintain and expand it, but NovuSurge does not require NovuPay as
                        its payment engine. Organizations can run NovuSurge with cashier-only collections, with an existing online payment provider, with multiple
                        payment integrations, or with NovuPay where that is the preferred fit.
                    </p>
                </section>

                <section id="challenge" class="doc-section">
                    <h2>Operational challenge</h2>
                    <div class="doc-section-kicker">What teams are trying to solve</div>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="card-elevated h-100">
                                <div class="card-body p-4">
                                    <h5 class="mb-3">Field constraints</h5>
                                    <p class="mb-0">
                                        Electric meter reading happens in real conditions, not ideal ones. Teams need route-based execution, account lookup, previous reading
                                        context, current balance information, and a practical way to continue operations even when connectivity is poor or unavailable.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card-elevated h-100">
                                <div class="card-body p-4">
                                    <h5 class="mb-3">Back-office pressure</h5>
                                    <p class="mb-0">
                                        Back-office users need traceable power billing computation, configurable charges, penalty logic, rereads, partial payments,
                                        customer support visibility, ageing reports, and a reliable way to reconcile all collection channels into the same ledger.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section id="platform" class="doc-section">
                    <h2>What NovuSurge includes</h2>
                    <div class="doc-section-kicker">Operational building blocks</div>
                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="card-elevated h-100">
                                <div class="card-body p-4">
                                    <h5>Meter reading operations</h5>
                                    <p class="mb-0">
                                        Route- or zone-based schedules, read and unread tracking, meter-reader-friendly search, prior reading context, consumption
                                        review, high-consumption notes, and a field workflow designed around actual operational runs.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card-elevated h-100">
                                <div class="card-body p-4">
                                    <h5>Billing and computation</h5>
                                    <p class="mb-0">
                                        One reading event becomes one bill, then expands into formal breakdowns and discounts. Generation and distribution rates,
                                        taxes, subsidies, arrears, due dates, penalties, advance payments, partial payments, and rereads are handled inside the billing model.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card-elevated h-100">
                                <div class="card-body p-4">
                                    <h5>Collections and reporting</h5>
                                    <p class="mb-0">
                                        Collections live alongside ledgers, dashboards, monthly summaries, account views, ageing analysis, payment-method filters,
                                        and export-ready reports so operations and finance teams can work from the same source of truth.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section id="architecture" class="doc-section">
                    <h2>Platform architecture</h2>
                    <div class="doc-section-kicker">How the operating model is structured</div>
                    <p>
                        NovuSurge is organized as an operational system of record rather than a loose set of disconnected tools. Account records, field data, billing outputs,
                        payment states, and reports are connected through a consistent workflow. That makes it easier to inspect why a bill exists, what inputs created it,
                        what adjustments were applied, and whether it has been paid in full, partially paid, or carried forward.
                    </p>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="card-elevated h-100">
                                <div class="card-body p-4">
                                    <h5 class="mb-3">Operational modules</h5>
                                    <p class="mb-0">
                                        Typical modules include dashboards, user and role management, account views, meter reading screens, billing, payments,
                                        reports, support workflows, settings, and operational sync screens for offline and online activities.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card-elevated h-100">
                                <div class="card-body p-4">
                                    <h5 class="mb-3">Data relationship model</h5>
                                    <p class="mb-0">
                                        A core design principle is traceability. One electric meter reading record can produce one bill, and that bill can expand into
                                        multiple line items, discounts, payment events, and ledger updates while preserving the full transaction history.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section id="workflow" class="doc-section">
                    <h2>Core workflow</h2>
                    <div class="doc-section-kicker">From meter reading data to posted payment</div>
                    <div class="card-elevated" style="background: #fff9f6;">
                        <div class="card-body p-4 p-md-5">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <h5 class="mb-3">1. Read and capture</h5>
                                    <p>
                                        Teams receive the operational data they need before going into the field, including account records, latest known readings,
                                        balances, rates, customer classifications, and supporting billing context. They can continue recording
                                        offline and synchronize once a connection is available.
                                    </p>
                                    <h5 class="mb-3">2. Merge and validate</h5>
                                    <p class="mb-0">
                                        Field submissions do not need to enter the official ledger immediately. They can pass through a review or merge layer first,
                                        allowing administrators to validate, approve, and normalize field data without blocking technician productivity.
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <h5 class="mb-3">3. Bill and collect</h5>
                                    <p>
                                        Billing is computed using the organization's rules: previous balances, generation and distribution charges, taxes,
                                        discounts, due dates, penalties, and account-specific conditions. The result can then surface in statements of account,
                                        ledger views, cashier screens, customer portals, and exports.
                                    </p>
                                    <h5 class="mb-3">4. Reconcile and report</h5>
                                    <p class="mb-0">
                                        Regardless of whether payment was collected over the counter, through a third-party gateway, or through NovuPay, the objective
                                        is the same: update one operational picture. Management can then monitor balances, collections, field completion, and
                                        downloadable reports without switching between disconnected tools.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section id="payments" class="doc-section">
                    <h2>Collections and payment flexibility</h2>
                    <div class="doc-section-kicker">Payment-agnostic by design</div>
                    <p>
                        NovuSurge is not tied to a single collection method. Some electric utilities may use only cashier operations. Others may support bank transfers,
                        QR payments, links, over-the-counter references, or one or more online payment gateways. NovuSurge is designed to sit above those collection
                        choices as the operating layer that records account state, billing state, and posted payment status.
                    </p>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="card-elevated h-100">
                                <div class="card-body p-4">
                                    <h5 class="mb-3">Cashier and in-office collection</h5>
                                    <p class="mb-0">
                                        For organizations with strong in-person collection workflows, NovuSurge supports official receipt-oriented operations,
                                        payment status updates, partial payments, advance payments, discount handling, and ledger posting without requiring any external gateway.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card-elevated h-100">
                                <div class="card-body p-4">
                                    <h5 class="mb-3">Online collection integrations</h5>
                                    <p class="mb-0">
                                        When online payment integrations are enabled, NovuSurge can track the operational status of transactions, review sync activity,
                                        and bring completed payment information back into the same administrative workflow used for billing and reporting.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section id="governance" class="doc-section">
                    <h2>Data, controls, and auditability</h2>
                    <div class="doc-section-kicker">Why operations teams trust the workflow</div>
                    <p>
                        Electric utility billing environments require more than convenience. They require explainability. NovuSurge is designed so operations teams
                        can review what happened at each stage: what field data was captured, what was merged, what billing rules were applied, what the resulting
                        statement looked like, and how payment status changed over time.
                    </p>
                    <p>
                        This supports stronger internal controls, easier dispute handling, cleaner reconciliation, and better reporting. It also makes the platform
                        practical for organizations that need approval layers, export trails, operational review screens, and confidence that ledger outcomes can be traced back to source activity.
                    </p>
                </section>

                <section id="benefits" class="doc-section">
                    <h2>Operational benefits</h2>
                    <div class="doc-section-kicker">Why teams adopt the model</div>
                    <ul>
                        <li>Meter reading continuity even when field teams work in low-connectivity areas.</li>
                        <li>More traceable billing through a clear service-to-bill-to-payment chain.</li>
                        <li>Cleaner reconciliation across cashier, integrated, and externally initiated collections.</li>
                        <li>More collection flexibility without locking the organization into one provider.</li>
                        <li>Stronger customer service through self-service account visibility and better support context.</li>
                        <li>Better management visibility through ledgers, dashboards, exports, and operational reports.</li>
                        <li>Less spreadsheet dependence for ageing, monitoring, and daily execution.</li>
                    </ul>
                </section>

                <section id="implementation" class="doc-section">
                    <h2>Implementation model</h2>
                    <div class="doc-section-kicker">How organizations typically roll out NovuSurge</div>
                    <p>
                        NovuSurge can be introduced in phases. A common rollout starts with account data, meter reading workflows, and billing generation, followed by
                        reports and management dashboards, then customer-facing account access, and finally online payment integrations where needed.
                    </p>
                    <p>
                        This phased model allows organizations to gain operational value early without waiting for every module to go live at once. It also makes it
                        easier to preserve existing cashier workflows, approval paths, and reporting expectations during transition.
                    </p>
                </section>

                <section id="who-its-for" class="doc-section">
                    <h2>Best-fit organizations</h2>
                    <div class="doc-section-kicker">Where NovuSurge is strongest</div>
                    <p>
                        NovuSurge is best suited for electric cooperatives, power distribution utilities, and collection-oriented organizations that need meter reading activity,
                        account management, power billing computation, customer account visibility, and flexible collections in one operational environment.
                    </p>
                    <div class="card-elevated mt-4">
                        <div class="card-body p-4 p-md-5">
                            <h4 class="mb-3">Discuss your implementation</h4>
                            <p class="mb-4">
                                If you want to use NovuSurge as the operating layer behind your billing and collection workflows, our team can walk you through
                                the product model, rollout considerations, integration options, and how it can fit your current operating environment.
                            </p>
                            <div class="whitepaper-actions">
                                <a href="{{ route('demo') }}" class="btn btn-primary me-2" style="padding: 10px 24px;">Book demo</a>
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
