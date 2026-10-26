@extends('layouts.payment')

@section('title', (!empty($isSample) ? 'Sample Checkout' : 'Checkout') . ' | NovuPay')

@section('base')
@php
    $isSample = !empty($isSample);
    $period = ($bill->bill_period_from && $bill->bill_period_to)
        ? \Carbon\Carbon::parse($bill->bill_period_from)->format('M d, Y') . ' – ' . \Carbon\Carbon::parse($bill->bill_period_to)->format('M d, Y')
        : null;
    $due = $bill->due_date ? \Carbon\Carbon::parse($bill->due_date)->timezone('Asia/Manila')->format('M d, Y') : null;
    $paid = !empty($bill->isPaid);
@endphp

<div class="checkout-page">
    <div class="checkout-card">
        <div class="brand">
            <img src="{{ asset('images/novupay-logo.png') }}" alt="NovuPay">
            <div>
                <div class="merchant">Sta. Rita Water District</div>
                @if($isSample)
                    <div class="sample-pill">Sample checkout</div>
                @endif
            </div>
        </div>

        <p class="sample-note">
            @if($isSample)
                This page is a preview of the bill checkout. It does not collect payment.
            @else
                Review your bill details below. Online charging through NovuPay is being enabled; no bank charge is collected from this screen yet.
            @endif
        </p>

        <div class="account">
            <div class="account-name">{{ $bill->account_name ?: 'Sta. Rita Customer' }}</div>
            <div class="account-meta">Account {{ $bill->account_no ?: '—' }}</div>
            @if(!empty($bill->address))
                <div class="account-meta">{{ $bill->address }}</div>
            @endif
        </div>

        <dl class="facts">
            <div><dt>Bill No.</dt><dd>{{ $bill->reference_no }}</dd></div>
            @if($period)
                <div><dt>Period</dt><dd>{{ $period }}</dd></div>
            @endif
            @if($due)
                <div><dt>Due date</dt><dd>{{ $due }}@if($pastDue) <span class="late">Past due</span>@endif</dd></div>
            @endif
            @if($bill->consumption !== null)
                <div>
                    <dt>Consumption</dt>
                    <dd>{{ $bill->previous_reading ?? '—' }} → {{ $bill->present_reading ?? '—' }} · {{ $bill->consumption }} cu.m</dd>
                </div>
            @endif
        </dl>

        <div class="lines">
            @forelse($lines as $line)
                @if(strtolower((string) $line->name) === 'system fee')
                    @continue
                @endif
                <div class="line">
                    <span>{{ $line->name }}</span>
                    <span>₱{{ number_format((float) $line->amount, 2) }}</span>
                </div>
            @empty
                <div class="line">
                    <span>Amount due</span>
                    <span>₱{{ number_format($amountDue, 2) }}</span>
                </div>
            @endforelse
            <div class="line">
                <span>Amount due</span>
                <span>₱{{ number_format($amountDue, 2) }}</span>
            </div>
            <div class="line muted" id="fee-line">
                <span>Convenience fee</span>
                <span id="fee-amount"></span>
            </div>
            <div class="line total">
                <span>Total to pay</span>
                <span id="total-amount"></span>
            </div>
        </div>

        <h2>Pay with</h2>
        <div class="methods">
            <button type="button" class="method" data-method="qrph">
                <img src="{{ asset('images/other-banks/qrph.png') }}" alt="">
                <span>QR Ph</span>
            </button>
            <button type="button" class="method" data-method="gcash">
                <img src="{{ asset('images/other-banks/gcash.png') }}" alt="">
                <span>GCash</span>
            </button>
        </div>

        <div class="qr-box" id="qr-box">
            <div class="qr">{!! $sampleQr !!}</div>
            <p>
                @if($isSample)
                    Sample QR only. Scanning it does not pay this bill.
                @else
                    QR Ph checkout preview. Scanning this code does not pay this bill yet.
                @endif
            </p>
        </div>

        <button type="button" class="pay" id="pay-button" @if($paid) disabled @endif>
            {{ $paid ? 'This bill is already paid' : 'Pay' }}
        </button>
        <p class="footnote">Powered by NovuPay</p>

        <div class="notice" id="notice" hidden>
            @if($isSample)
                This is a sample checkout. No payment was sent.
            @else
                No payment was sent. Your bill details are saved. Please pay at the Sta. Rita Water District office, or try again when online charging is available.
            @endif
        </div>
    </div>
</div>

<style>
    body { background: #eef3f7; margin: 0; }
    .checkout-page {
        min-height: 100vh;
        display: flex;
        justify-content: center;
        padding: 24px 16px 40px;
    }
    .checkout-card {
        width: 100%;
        max-width: 440px;
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(16, 42, 67, 0.08);
        padding: 22px 20px 18px;
    }
    .brand { display: flex; gap: 12px; align-items: center; }
    .brand img { height: 36px; width: auto; }
    .merchant { font-weight: 700; color: #16324f; }
    .sample-pill {
        display: inline-block;
        margin-top: 4px;
        background: #fff4d6;
        color: #8a5a00;
        font-size: 12px;
        font-weight: 700;
        border-radius: 999px;
        padding: 2px 8px;
    }
    .sample-note { color: #5c6b7a; font-size: 14px; margin: 14px 0; }
    .account-name { font-size: 20px; font-weight: 700; color: #16324f; }
    .account-meta { color: #5c6b7a; font-size: 14px; }
    .facts { margin: 16px 0; }
    .facts div {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        padding: 6px 0;
        border-top: 1px solid #eef2f5;
        font-size: 14px;
    }
    .facts dt { color: #5c6b7a; font-weight: 500; }
    .facts dd { margin: 0; text-align: right; font-weight: 600; color: #16324f; }
    .late { color: #b42318; font-size: 12px; }
    .lines { border-top: 1px solid #e6edf3; margin-top: 8px; }
    .line {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        font-size: 15px;
        color: #16324f;
    }
    .line.muted { color: #5c6b7a; }
    .line.total {
        font-size: 18px;
        font-weight: 800;
        border-top: 1px solid #e6edf3;
        margin-top: 4px;
    }
    h2 {
        font-size: 13px;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #5c6b7a;
        margin: 18px 0 10px;
    }
    .methods { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
    .method {
        border: 2px solid #e6edf3;
        background: #fff;
        border-radius: 12px;
        padding: 10px;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 6px;
        font-weight: 700;
        color: #16324f;
    }
    .method img { height: 36px; width: auto; max-width: 100%; }
    .method.selected { border-color: #19577d; background: #f3f8fb; }
    .qr-box { text-align: center; margin-top: 16px; }
    .qr-box .qr svg { width: 168px; height: 168px; }
    .qr-box p { color: #5c6b7a; font-size: 13px; margin: 8px 0 0; }
    .pay {
        width: 100%;
        margin-top: 18px;
        border: 0;
        border-radius: 10px;
        background: #19577d;
        color: #fff;
        font-weight: 700;
        font-size: 16px;
        padding: 14px;
    }
    .pay:disabled { background: #9aa8b5; }
    .footnote { text-align: center; color: #8b99a6; font-size: 12px; margin: 10px 0 0; }
    .notice {
        margin-top: 12px;
        background: #f4f8fc;
        border: 1px solid #d6e6f5;
        color: #16324f;
        border-radius: 10px;
        padding: 12px;
        font-size: 14px;
    }
</style>
@endsection

@section('scripts')
<script>
    const fees = @json($fees);
    const amountDue = {{ json_encode($amountDue) }};
    const paid = {{ $paid ? 'true' : 'false' }};
    let method = @json($defaultMethod);

    const peso = (value) => '₱' + Number(value).toLocaleString('en-PH', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });

    function render() {
        const fee = fees[method];
        document.getElementById('fee-amount').textContent = peso(fee);
        document.getElementById('total-amount').textContent = peso(amountDue + fee);
        document.querySelectorAll('.method').forEach((button) => {
            button.classList.toggle('selected', button.dataset.method === method);
        });
        document.getElementById('qr-box').hidden = method !== 'qrph';
        const pay = document.getElementById('pay-button');
        if (!paid) {
            pay.textContent = 'Pay ' + peso(amountDue + fee);
        }
    }

    document.querySelectorAll('.method').forEach((button) => {
        button.addEventListener('click', () => {
            method = button.dataset.method;
            document.getElementById('notice').hidden = true;
            render();
        });
    });

    document.getElementById('pay-button').addEventListener('click', () => {
        if (paid) return;
        document.getElementById('notice').hidden = false;
    });

    render();
</script>
@endsection
