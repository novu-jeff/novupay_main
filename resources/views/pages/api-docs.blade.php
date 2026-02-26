@extends('layouts.app')

@section('base')
<div class="page-section page-section--light">
    <div class="container page-section-inner" style="max-width: 1100px;">
        <div class="section-heading">
            <div class="section-kicker">API documentation</div>
            <div class="section-title">Novupay API &amp; integration guide</div>
            <div class="section-subtitle">
                High‑level documentation for integrating with Novupay as your payment aggregator. Detailed endpoint references can be added here as your API evolves.
            </div>
        </div>

        <div class="doc-layout">
            <aside class="doc-sidebar">
                <div class="doc-toc-title">On this page</div>
                <ul>
                    <li><a href="#introduction">Introduction</a></li>
                    <li><a href="#authentication">Authentication</a></li>
                    <li><a href="#payments-api">Payments API</a></li>
                    <li><a href="#webhooks">Webhooks</a></li>
                    <li><a href="#errors">Errors &amp; idempotency</a></li>
                    <li><a href="#examples">Code examples</a></li>
                </ul>
            </aside>

            <div class="doc-content">
                <section id="introduction" class="doc-section">
                    <h2>Introduction</h2>
                    <div class="doc-section-kicker">Overview</div>
                    <p>
                        The Novupay API lets you accept and manage payments across multiple channels (banks, e‑wallets, over‑the‑counter partners) through a single
                        integration. Use this section as a living guide: you can expand it later with full endpoint reference tables, diagrams, and flow examples.
                    </p>
                    <p>
                        Base URL (production): <code>https://novupay.ph/api</code><br>
                        Base URL (sandbox / placeholder): <code>https://sandbox.novupay.ph/api</code> <em>(optional; configure based on your environment)</em>
                    </p>
                </section>

                <section id="authentication" class="doc-section">
                    <h2>Authentication</h2>
                    <div class="doc-section-kicker">API keys</div>
                    <p>
                        Novupay uses bearer tokens for authenticating API requests. In your actual implementation, you can add details here about how keys are created,
                        rotated, and scoped (for example: project‑level keys, environment‑specific keys, or OAuth client credentials).
                    </p>
                    <p>
                        All requests should include an <code>Authorization: Bearer YOUR_API_KEY</code> header. You can add tables for scopes and key types here as the
                        platform matures.
                    </p>
                </section>

                <section id="payments-api" class="doc-section">
                    <h2>Payments API</h2>
                    <div class="doc-section-kicker">High‑level structure</div>
                    <p>
                        The Payments API is centered around a small set of resources such as payment intents, payment methods, and channel‑specific
                        instructions. The exact shape of your production API can evolve over time, but a common starting point is to expose endpoints like
                        <code>POST /payment/create</code> and <code>GET /payment/{id}</code> for creating and retrieving individual payments.
                    </p>
                    <p>
                        Below is a representative example of a create‑payment request. You can adjust the fields, validation rules, and channel options as you
                        finalize the platform:
                    </p>
                    <pre><code>POST /payment/create
Content-Type: application/json
Authorization: Bearer YOUR_API_KEY

{
    "amount": 1000.00,
    "currency": "PHP",
    "reference_no": "REF-123456",
    "description": "Sample payment",
    "channel": "auto",          // e.g. 'bank', 'ewallet', 'counter', or 'auto'
    "callback_url": "https://your-app.test/payments/callback",
    "metadata": {
        "customer_id": "CUST-001",
        "notes": "Optional field for internal tracking"
    }
}</code></pre>
                    <p class="mt-3">
                        In your live documentation you can extend this section with tables of request parameters and validation rules, channel‑specific notes
                        (e.g., e‑wallet vs bank transfer vs over‑the‑counter), and detailed response examples for successful and failed payments.
                    </p>
                </section>

                <section id="webhooks" class="doc-section">
                    <h2>Webhooks</h2>
                    <div class="doc-section-kicker">Real‑time notifications</div>
                    <p>
                        Use webhooks to receive real‑time updates about payment statuses, reversals, and reconciliation events. This area is reserved for describing
                        how to register webhook URLs, verify signatures, and handle retries in your application.
                    </p>
                    <p>
                        You can add:
                    </p>
                    <ul>
                        <li>Event types (e.g., <code>payment.succeeded</code>, <code>payment.failed</code>, <code>payment.settled</code>).</li>
                        <li>Payload examples and verification steps.</li>
                        <li>Recommended retry and backoff strategies.</li>
                    </ul>
                </section>

                <section id="errors" class="doc-section">
                    <h2>Errors &amp; idempotency</h2>
                    <div class="doc-section-kicker">Best practices</div>
                    <p>
                        Document common error structures, status codes, and recommended recovery behaviors here. You may also reserve space for idempotency keys
                        (so clients can safely retry requests without creating duplicate payments).
                    </p>
                    <p>
                        Example placeholders:
                    </p>
                    <ul>
                        <li>Standard error response format (e.g., <code>code</code>, <code>message</code>, <code>details</code>).</li>
                        <li>Channel‑specific failure reasons and remediation steps.</li>
                        <li>How to use an <code>Idempotency-Key</code> header for create‑payment operations.</li>
                    </ul>
                </section>

                <section id="examples" class="doc-section">
                    <h2>Code examples</h2>
                    <div class="doc-section-kicker">Quick start samples</div>
                    <p>
                        Use these examples as starting points. You can expand this area with more languages, SDK snippets, and environment‑specific notes as the
                        platform grows.
                    </p>

                    <h4 class="mt-3">PHP example</h4>
<pre><code>$curl = curl_init();

curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://novupay.ph/api/payment/create',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode([
        'amount'       =&gt; 1000.00,
        'currency'     =&gt; 'PHP',
        'reference_no' =&gt; 'REF-123456',
        'email'        =&gt; 'customer@example.com',
    ]),
    CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json',
        'Authorization: Bearer YOUR_API_KEY',
    ),
));

$response = curl_exec($curl);
curl_close($curl);

$data = json_decode($response, true);</code></pre>

                    <h4 class="mt-4">JavaScript example</h4>
<pre><code>fetch('https://novupay.ph/api/payment/create', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'Authorization': 'Bearer YOUR_API_KEY',
    },
    body: JSON.stringify({
        amount: 1000.00,
        currency: 'PHP',
        reference_no: 'REF-123456',
        email: 'customer@example.com',
    }),
})
    .then(response =&gt; response.json())
    .then(data =&gt; console.log(data))
    .catch(error =&gt; console.error('Error:', error));</code></pre>

                    <div class="card-elevated mt-4" style="background: #f8f9fa;">
                        <div class="card-body p-4">
                            <h5 class="mb-2">Next steps</h5>
                            <p class="mb-0">
                                As you finalize your API surface, you can replace these placeholders with real endpoint tables, authentication flows, and
                                channel‑specific integration notes. This page is structured to grow with your documentation needs.
                            </p>
                        </div>
                    </div>
                </section>

                <section class="doc-section">
                    <h2>Need help?</h2>
                    <div class="doc-section-kicker">Developer support</div>
                    <p>
                        Our developer support team can assist with integration questions, testing strategies, and rollout planning. Use the contact options below to
                        get in touch.
                    </p>
                    <p>
                        <a href="{{ route('contact') }}" class="btn btn-primary me-2" style="padding: 10px 24px;">Contact support</a>
                        <a href="mailto:api@novupay.ph" class="btn btn-outline-primary" style="padding: 10px 24px;">api@novupay.ph</a>
                    </p>
                </section>
            </div>
        </div>
    </div>
</div>
@endsection
