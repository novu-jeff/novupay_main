@extends('layouts.app')

@section('title', 'NovuSurge Electric Meter Inspection Module | Technical White Paper | NovuPay')
@section('meta_description', 'Technical white paper: electric meter inspection reference architecture, diagrams, security model, Philippine regulatory mapping (ERC, DPA), CIS and GIS integration, KPI and ROI framing, sample reports, pilot methodology.')

@section('base')
<div class="page-section page-section--light">
    <div class="container page-section-inner" style="max-width: 1160px;">
        <div class="section-heading" style="margin-bottom: 28px;">
            <div class="section-kicker">Technical white paper</div>
            <div class="section-title">Digital electric meter inspection: reference architecture and operational model</div>
            <div class="section-subtitle" style="max-width: 860px;">
                This document examines how field inspection workflows are commonly digitized for distribution utilities: structured capture, asynchronous synchronization,
                centralized persistence, and reporting. It references implementation patterns found in NovuSurge utility deployments (mobile client, REST APIs, administrative review, CSV export).
                It is intended for technical staff, compliance officers, and decision-makers evaluating procurement fit—not as a substitute for project-specific terms of reference or legal advice.
            </div>
        </div>

        <div class="card-elevated mb-4" style="border-radius: 12px; border: 1px solid rgba(15,23,42,0.08);">
            <div class="card-body p-3 p-md-4" style="background: #f8fafc;">
                <div class="row g-3 align-items-center small text-muted">
                    <div class="col-md-8">
                        <strong class="text-dark">Document control</strong>
                        <span class="d-block d-md-inline d-md-ms-2">Subject: Electric meter inspection module · Version 1.1 · April 7, 2026 · Audience: utilities, LGUs, IT, auditors, integrators</span>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <span>Status: reference / stakeholder review</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-elevated mb-5" style="border-radius: 20px; overflow: hidden;">
            <div class="card-body p-4 p-md-5" style="background: linear-gradient(135deg, #0f172a 0%, #6b1f00 55%, #fee2d3 180%); color: #fff;">
                <div class="row g-4 align-items-center">
                    <div class="col-lg-8">
                        <div class="pill mb-3" style="background: rgba(255,255,255,0.14); color: #fff;">Scope summary</div>
                        <h1 class="whitepaper-hero-title" style="font-size: 36px; font-weight: 700; line-height: 1.15; margin-bottom: 16px;">
                            From structured field evidence to queryable records and exportable reports
                        </h1>
                        <p style="font-size: 16px; line-height: 1.8; color: rgba(255,255,255,0.88); margin-bottom: 0;">
                            Periodic metering asset review supports billing integrity, technical validation, and dispute resolution. Digitizing inspections does not replace regulatory or contractual obligations;
                            it improves traceability, repeatability, and the speed at which evidence can be reviewed—when design, security, and data governance are addressed explicitly.
                        </p>
                    </div>
                    <div class="col-lg-4">
                        <div class="whitepaper-hero-stats" style="display: grid; gap: 12px;">
                            <div style="padding: 16px 18px; border-radius: 16px; background: rgba(15,23,42,0.34); border: 1px solid rgba(255,255,255,0.18);">
                                <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.08em; opacity: 0.75;">Capture</div>
                                <div style="font-size: 18px; font-weight: 700;">Offline-capable forms + media</div>
                            </div>
                            <div style="padding: 16px 18px; border-radius: 16px; background: rgba(15,23,42,0.34); border: 1px solid rgba(255,255,255,0.18);">
                                <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.08em; opacity: 0.75;">Integration surface</div>
                                <div style="font-size: 18px; font-weight: 700;">REST + tabular export</div>
                            </div>
                            <div style="padding: 16px 18px; border-radius: 16px; background: rgba(15,23,42,0.34); border: 1px solid rgba(255,255,255,0.18);">
                                <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.08em; opacity: 0.75;">Review</div>
                                <div style="font-size: 18px; font-weight: 700;">Filter, sort, audit trail</div>
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
                    <li><a href="#problem-statement">Problem statement</a></li>
                    <li><a href="#industry-context">Industry and regulatory context</a></li>
                    <li><a href="#proposed-solution">Proposed solution</a></li>
                    <li><a href="#technical-design">Technical design</a></li>
                    <li><a href="#security-compliance">Security and compliance model</a></li>
                    <li><a href="#integration">Integration capabilities</a></li>
                    <li><a href="#evidence-metrics">Evidence, metrics, and ROI framing</a></li>
                    <li><a href="#sample-outputs">Sample outputs and reports</a></li>
                    <li><a href="#case-study">Pilot and case-study pattern</a></li>
                    <li><a href="#benefits-roi">Benefits and return on investment</a></li>
                    <li><a href="#conclusion">Conclusion</a></li>
                    <li><a href="#glossary">Glossary</a></li>
                </ul>
            </aside>

            <div class="doc-content">
                <section id="executive-summary" class="doc-section">
                    <h2>Executive summary</h2>
                    <div class="doc-section-kicker">Analytical overview</div>
                    <p>
                        Electric distribution utilities and many electric cooperatives maintain large populations of customer meters. Asset inspection—verifying installed equipment, seals, and technical attributes against records—is a recurring operational and governance activity.
                        Manual, paper-heavy processes tend to produce uneven data quality, weak linkage between identity/time/location and evidence, and slow consolidation for supervision and audit.
                    </p>
                    <p>
                        A common mitigation is a <strong>digitized inspection pipeline</strong>: structured data entry on mobile devices, optional geolocation and photographs, local persistence for intermittent connectivity, batch upload to a server, and administrative tools for validation, monitoring, and export.
                        This paper describes that pattern as implemented in reference NovuSurge deployments: REST endpoints for sync and listing, separate image upload, role-scoped web review, and CSV export aligned to the persisted schema.
                    </p>
                    <p class="mb-0">
                        The document does not certify regulatory compliance for any specific organization; it maps <strong>where</strong> technical controls and organizational policies typically intersect (data protection, access control, evidence retention). Numeric “results” below include an <strong>illustrative scenario</strong> for discussion of ROI methodology—not measured benchmarks for a named deployment.
                    </p>
                </section>

                <section id="problem-statement" class="doc-section">
                    <h2>Problem statement</h2>
                    <div class="doc-section-kicker">Operational and governance gaps</div>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="card-elevated h-100">
                                <div class="card-body p-4">
                                    <h5 class="mb-3">Data quality and standardization</h5>
                                    <p class="mb-0">
                                        Paper forms and ad hoc spreadsheets often yield incomplete fields, inconsistent vocabulary for meter types, and ambiguous handling of edge cases (for example, unreadable serial numbers). That undermines analytics, reconciliation with billing master data, and repeatability across teams.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card-elevated h-100">
                                <div class="card-body p-4">
                                    <h5 class="mb-3">Auditability and evidence</h5>
                                    <p class="mb-0">
                                        Regulators, internal audit, and customer dispute processes benefit from attributable records: who captured what, when, and with what corroboration (photo, coordinates). Disconnected processes make it harder to reconstruct a defensible chain of evidence.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card-elevated h-100">
                                <div class="card-body p-4">
                                    <h5 class="mb-3">Field connectivity</h5>
                                    <p class="mb-0">
                                        Field teams cannot assume continuous connectivity. Systems that require live submission per stop often fail in practice; offline-first designs defer upload until the network is usable.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card-elevated h-100">
                                <div class="card-body p-4">
                                    <h5 class="mb-3">Visibility and reporting latency</h5>
                                    <p class="mb-0">
                                        Supervision and loss-analysis teams need timely aggregates (completion rates, seal status distribution, geographic coverage). Manual consolidation delays insight and increases reconciliation cost.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section id="industry-context" class="doc-section">
                    <h2>Industry and regulatory context (Philippines)</h2>
                    <div class="doc-section-kicker">Mapping—not certification</div>
                    <p>
                        In the Philippines, electric power involves multiple institutional layers (generation, transmission, distribution). Customer-facing metering and distribution service are subject to <strong>Energy Regulatory Commission (ERC)</strong> oversight for regulated entities, under the policy framework established by <strong>Republic Act No. 9136</strong> (EPIRA) and implementing rules.
                        Specific metering, inspection, or service-quality obligations are often articulated in <strong>ERC resolutions, distribution codes, and distribution utility or electric cooperative service rules</strong>; these vary by franchise and regulatory classification.
                    </p>
                    <p>
                        <strong>Personal data</strong> processed in inspection workflows (customer identifiers, addresses if present in account master data, photographs of premises) falls under the <strong>Data Privacy Act of 2012 (Republic Act No. 10173)</strong> and NPC guidance. Organizations define lawful basis, retention, access, and breach response in privacy notices and internal policies.
                    </p>
                    <p>
                        <strong>Electric cooperatives</strong> have additional governance context (e.g., cooperative law, supervisory relationships). A technical platform does not substitute for board-approved policies or regulatory filings; it should support <strong>documentation and export</strong> that staff can use when demonstrating process controls.
                    </p>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle" style="background: #fff;">
                            <thead class="table-light">
                                <tr>
                                    <th>Topic</th>
                                    <th>Typical organizational alignment</th>
                                    <th>Platform relevance</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Metering and inspection evidence</td>
                                    <td>ERC rules, DU/EC internal procedures, service contracts</td>
                                    <td>Structured fields, timestamps, optional photo and coordinates, export for audit</td>
                                </tr>
                                <tr>
                                    <td>Customer / employee personal data</td>
                                    <td>DPA, NPC registration and policies where applicable</td>
                                    <td>Access control, minimization, retention, secure transport, logging discipline</td>
                                </tr>
                                <tr>
                                    <td>Operational accountability</td>
                                    <td>Internal control frameworks, QA on field data</td>
                                    <td>Device and user attribution, non-repudiation via server logs (policy-dependent)</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <section id="proposed-solution" class="doc-section">
                    <h2>Proposed solution</h2>
                    <div class="doc-section-kicker">Reference decomposition</div>
                    <p>
                        The reference model separates <strong>capture</strong> (mobile), <strong>ingestion</strong> (sync and media APIs), <strong>persistence</strong> (relational store and object storage for images), and <strong>consumption</strong> (administrative UI, JSON list API, CSV export for BI).
                        Staging endpoints may mirror production contracts so pilots can validate behavior without writing to production tables.
                    </p>
                    <ul>
                        <li><strong>Mobile client</strong> — Authentication, account search (e.g., by account number or meter serial against master data), structured form capture, local database, user-initiated sync.</li>
                        <li><strong>Sync API</strong> — Batch JSON upsert keyed by <code>reference_no</code>.</li>
                        <li><strong>Image API</strong> — Multipart upload after the inspection row exists; server stores file and updates <code>image_url</code>.</li>
                        <li><strong>List API</strong> — Authenticated, paginated, filterable, sortable JSON for integration and reconciliation.</li>
                        <li><strong>Web operations</strong> — Filter forms, tabular views, CSV export for downstream analysis.</li>
                    </ul>
                </section>

                <section id="technical-design" class="doc-section">
                    <h2>Technical design</h2>
                    <div class="doc-section-kicker">Architecture and workflows</div>

                    <h3 class="h5 mt-4">Logical system architecture</h3>
                    <p class="small text-muted mb-2">Diagram: major components and trust boundaries. Deployment topology (single region, HA, cloud provider) is organization-specific.</p>
                    <div class="card-elevated mb-4 p-3 p-md-4" style="background: #fff; overflow-x: auto;">
                        <div class="mermaid mermaid-diagram">
flowchart TB
    subgraph field["Field zone"]
        MOB["Mobile app (Flutter, local DB)"]
    end
    subgraph dmz["API zone (HTTPS)"]
        API["Application server (REST)"]
        AUTH["Token authentication"]
    end
    subgraph data["Data zone"]
        DB[("Relational DB: meter_inspections")]
        OBJ["Object storage: meter photos"]
    end
    subgraph ops["Operations zone"]
        WEB["Admin web UI"]
        BI["BI / GIS / CIS"]
    end
    MOB -->|"TLS JSON batch"| API
    MOB -->|"TLS multipart image"| API
    API --> AUTH
    API --> DB
    API --> OBJ
    WEB -->|"HTTPS"| API
    BI -->|"List API or CSV export"| API
                        </div>
                    </div>

                    <h3 class="h5 mt-4">Inspection lifecycle: capture to reporting</h3>
                    <p class="small text-muted mb-2">Diagram: validation gates are organizational; the platform supports technical validation (required fields, existence of row before image).</p>
                    <div class="card-elevated mb-4 p-3 p-md-4" style="background: #fff; overflow-x: auto;">
                        <div class="mermaid mermaid-diagram">
flowchart LR
    A["Search account (QR or input)"] --> B["Complete inspection + optional GPS or photo"]
    B --> C["Save local pending sync"]
    C --> D["POST batch sync upsert"]
    D --> E{"Row persisted?"}
    E -->|yes| F["POST image if any"]
    E -->|retry| C
    F --> G["Server sets image_url"]
    G --> H["Staff review web UI"]
    H --> I["Reports filters CSV API"]
    I --> J["Optional CIS or GIS reconciliation"]
                        </div>
                    </div>

                    <h3 class="h5 mt-4">Data model (reference)</h3>
                    <p>
                        Persisted attributes align with a normalized inspection row: <code>reference_no</code> (unique), <code>account_no</code>, meter brand/type/form, <code>wire</code>, <code>meter_class</code>, <code>meter_serial</code> with <code>meter_serial_not_readable</code>, <code>erc_seal</code>, coordinates, <code>device_id</code>, <code>created_at_mobile</code>, <code>remarks</code>, <code>image_url</code>, soft-delete flags where implemented.
                    </p>

                    <h3 class="h5 mt-4">Synchronization behavior</h3>
                    <p>
                        Offline-first: write locally, then batch POST to <code>/api/sync/inspections</code> (and staging equivalent). Image upload typically requires the inspection row to exist. List endpoints enforce pagination caps and whitelisted sort columns to reduce abuse and stabilize query plans.
                    </p>
                </section>

                <section id="security-compliance" class="doc-section">
                    <h2>Security and compliance model</h2>
                    <div class="doc-section-kicker">Defense in depth (typical)</div>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="card-elevated h-100">
                                <div class="card-body p-4">
                                    <h5 class="mb-3">Transport and API surface</h5>
                                    <ul class="mb-0 small">
                                        <li>TLS 1.2+ for all client–server traffic in production.</li>
                                        <li>Separate production and staging base URLs; avoid cross-environment data leakage.</li>
                                        <li>Rate limiting and payload size limits at reverse proxy or application layer (deployment-specific).</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card-elevated h-100">
                                <div class="card-body p-4">
                                    <h5 class="mb-3">Authentication and authorization</h5>
                                    <ul class="mb-0 small">
                                        <li>Token-based access to protected inspection search and list routes.</li>
                                        <li>Role- or policy-based access to administrative screens (e.g., technician vs. staff).</li>
                                        <li>Device-scoped visibility where policy requires limiting inspectors to their own device identifier.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card-elevated h-100">
                                <div class="card-body p-4">
                                    <h5 class="mb-3">Data protection</h5>
                                    <ul class="mb-0 small">
                                        <li>Minimize fields displayed or exported to job function.</li>
                                        <li>Secure storage backends for images; backup and retention policies defined by the utility.</li>
                                        <li>Logging: operational logs should avoid capturing secrets or unnecessary PII.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card-elevated h-100">
                                <div class="card-body p-4">
                                    <h5 class="mb-3">Client device</h5>
                                    <ul class="mb-0 small">
                                        <li>OS updates, screen lock, MDM where deployed.</li>
                                        <li>Physical control of devices holding local databases and cached photos.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section id="integration" class="doc-section">
                    <h2>Integration capabilities</h2>
                    <div class="doc-section-kicker">Systems of record and analytics</div>
                    <p>
                        Inspection systems rarely replace a customer information system (CIS) or billing engine; they <strong>feed</strong> it with verified field evidence. Typical integration patterns:
                    </p>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle" style="background: #fff;">
                            <thead class="table-light">
                                <tr>
                                    <th>System</th>
                                    <th>Integration mechanism</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Billing / CIS</td>
                                    <td>Batch or API reconciliation on <code>account_no</code>, meter serial, class</td>
                                    <td>Master data sync must precede reliable search and duplicate detection.</td>
                                </tr>
                                <tr>
                                    <td>GIS / network maps</td>
                                    <td>Export latitude/longitude via CSV or API; ETL into GIS layers</td>
                                    <td>Accuracy depends on device GPS; governance defines acceptable use.</td>
                                </tr>
                                <tr>
                                    <td>BI (Power BI, etc.)</td>
                                    <td>Scheduled CSV ingestion or JSON pull from list API</td>
                                    <td>Model seal rates, completion by area, photo attachment rates.</td>
                                </tr>
                                <tr>
                                    <td>Payments (e.g., NovuPay)</td>
                                    <td>Orthogonal to inspection capture; shared only at customer account identifier</td>
                                    <td>Useful when the same utility runs billing and payment channels; not a prerequisite for inspection.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <section id="evidence-metrics" class="doc-section">
                    <h2>Evidence, metrics, and ROI framing</h2>
                    <div class="doc-section-kicker">What to measure</div>
                    <p>
                        Credible ROI arguments combine <strong>baseline</strong> and <strong>post-implementation</strong> measurements on agreed KPIs. The table below lists indicators commonly used; targets are set per organization.
                    </p>
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered align-middle" style="background: #fff;">
                            <thead class="table-light">
                                <tr>
                                    <th>KPI category</th>
                                    <th>Examples</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Throughput</td>
                                    <td>Inspections completed per inspector-week; sync success rate</td>
                                </tr>
                                <tr>
                                    <td>Data quality</td>
                                    <td>Share of records with photo; share with unreadable serial explicitly flagged vs. blank</td>
                                </tr>
                                <tr>
                                    <td>Timeliness</td>
                                    <td>Median time from field capture to server persistence</td>
                                </tr>
                                <tr>
                                    <td>Supervision</td>
                                    <td>Backlog of unsynced records; error rate on batch POST</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <p class="fw-semibold mb-2">Illustrative scenario (not empirical benchmark)</p>
                    <p class="small text-muted mb-2">
                        Hypothetical composite for methodology discussion only. Actual results depend on territory, training, and baseline process maturity.
                    </p>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle" style="background: #fff;">
                            <thead class="table-light">
                                <tr>
                                    <th>Indicator</th>
                                    <th>Illustrative “before” (paper-heavy)</th>
                                    <th>Illustrative “after” (digitized pipeline)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Central visibility delay</td>
                                    <td>2–4 weeks to consolidated spreadsheet</td>
                                    <td>Same-day to next-day after sync (connectivity dependent)</td>
                                </tr>
                                <tr>
                                    <td>Photo evidence rate</td>
                                    <td>Variable; often missing</td>
                                    <td>Higher attach rate when required by procedure + mobile prompts</td>
                                </tr>
                                <tr>
                                    <td>Audit preparation</td>
                                    <td>Manual file assembly</td>
                                    <td>Filter + export of structured rows and URLs</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <section id="sample-outputs" class="doc-section">
                    <h2>Sample outputs and reports</h2>
                    <div class="doc-section-kicker">What reviewers actually receive</div>
                    <p>
                        Reference deployments expose <strong>CSV export</strong> with columns aligned to the database (e.g., reference, account, meter attributes, seal flag, coordinates, device, timestamps, remarks, image URL). Administrative screens support <strong>filtering</strong> by reference, account, serial, brand, device, date window, seal flag, and presence of image.
                    </p>
                    <p>
                        JSON list APIs return paginated items plus metadata (page, per_page, total) for programmatic reporting. These outputs are suitable inputs to <strong>management dashboards</strong> (completion by period, seal compliance share, geographic coverage) when combined with area codes from CIS if joined downstream.
                    </p>
                </section>

                <section id="case-study" class="doc-section">
                    <h2>Pilot and case-study pattern</h2>
                    <div class="doc-section-kicker">How to structure a defensible pilot</div>
                    <ol>
                        <li><strong>Define scope</strong> — Geography, account population, and success criteria tied to KPIs above.</li>
                        <li><strong>Use staging</strong> — Exercise sync and image paths without production data risk.</li>
                        <li><strong>Parallel run (optional)</strong> — Compare paper and digital on a sample route to measure discrepancy rates.</li>
                        <li><strong>User acceptance</strong> — Sign-off from operations, IT security, and data protection stakeholders.</li>
                        <li><strong>Scale</strong> — Expand routes; monitor API volume and storage growth.</li>
                    </ol>
                    <p class="mb-0 small text-muted">
                        Published case studies with named utilities should cite measured figures from those engagements; this page does not substitute for those disclosures.
                    </p>
                </section>

                <section id="benefits-roi" class="doc-section">
                    <h2>Benefits and return on investment</h2>
                    <div class="doc-section-kicker">Expected value categories</div>
                    <ul>
                        <li><strong>Operational efficiency</strong> — Less manual transcription; faster supervisory review.</li>
                        <li><strong>Risk reduction</strong> — Stronger evidence chain for disputes and internal control narratives.</li>
                        <li><strong>Analytics</strong> — Structured data for loss analysis and asset inventory programs.</li>
                        <li><strong>Scalability</strong> — API pagination and chunked export support growing volume.</li>
                    </ul>
                    <p class="mb-0">
                        ROI should be computed from the utility’s own cost model (labor hours, error rework, regulatory exposure) against implementation and recurring costs—not inferred from generic marketing claims.
                    </p>
                </section>

                <section id="conclusion" class="doc-section">
                    <h2>Conclusion</h2>
                    <div class="doc-section-kicker">Summary</div>
                    <p>
                        Effective meter inspection digitization combines <strong>offline-capable capture</strong>, <strong>clear server contracts</strong> for data and media, <strong>role-appropriate review tools</strong>, and <strong>exportable evidence</strong> for audit and integration.
                        For Philippine utilities, alignment with <strong>ERC-regulated operational context</strong> and <strong>DPA obligations</strong> remains an organizational responsibility; the platform provides technical enablers—transport security, structured fields, access separation, and traceability hooks—that teams can map into their control frameworks.
                    </p>
                    <p class="mb-0">
                        Procurement teams should pair this technical reference with project-specific <strong>terms of reference</strong>, data processing agreements, and acceptance tests. For platform-level context on billing and collections, see the
                        <a href="{{ route('services.novusurge.whitepaper') }}">NovuSurge electric utilities white paper</a>.
                    </p>
                </section>

                <section id="glossary" class="doc-section">
                    <h2>Glossary</h2>
                    <div class="doc-section-kicker">Terms</div>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle" style="background: #fff;">
                            <thead class="table-light">
                                <tr>
                                    <th>Term</th>
                                    <th>Definition</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>ERC</td>
                                    <td>Energy Regulatory Commission (Philippines)—regulates certain activities of electric market participants subject to its jurisdiction.</td>
                                </tr>
                                <tr>
                                    <td>EPIRA</td>
                                    <td>Republic Act No. 9136, the Electric Power Industry Reform Act.</td>
                                </tr>
                                <tr>
                                    <td>DPA</td>
                                    <td>Data Privacy Act of 2012 (Republic Act No. 10173).</td>
                                </tr>
                                <tr>
                                    <td>ERC seal (field)</td>
                                    <td>Observed seal condition on the installation, stored as a boolean in sync payloads in reference implementations.</td>
                                </tr>
                                <tr>
                                    <td>Sync</td>
                                    <td>Upload of locally stored inspection batches and associated images to central APIs.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="doc-section">
                    <div class="card-elevated mt-2" style="border: 1px solid rgba(15,23,42,0.08);">
                        <div class="card-body p-4 p-md-5">
                            <h4 class="mb-2 h5">Further information</h4>
                            <p class="mb-3 text-muted small">
                                For product demonstrations or integration discussions, contact channels are available through the site; engagements remain subject to scope and fit.
                            </p>
                            <div class="whitepaper-actions">
                                <a href="{{ route('contact') }}" class="btn btn-outline-primary" style="padding: 10px 24px;">Contact</a>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/mermaid@10/dist/mermaid.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof mermaid === 'undefined') {
            return;
        }
        mermaid.initialize({
            startOnLoad: false,
            theme: 'neutral',
            securityLevel: 'strict',
            flowchart: { useMaxWidth: true, htmlLabels: true }
        });
        mermaid.run({ querySelector: '.mermaid' }).catch(function () { /* ignore render edge cases */ });
    });
</script>
@endsection
