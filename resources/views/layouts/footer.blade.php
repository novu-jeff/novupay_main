<div>
    <div class="contact">
        <div class="container">
            <div class="overlay">
                <img src="{{asset('images/contact-banner.jpg')}}" alt="contact">        
            </div>
            <div>
                <h1>Ready to Elevate Your Payment Experience?</h1>
                <a href="{{ route('contact') }}" class="btn-primary" style="display:inline-block; margin-top:40px;">Get Started</a>
            </div>            
        </div>
    </div>
    <div class="footer">
        <div class="container">
            <div class="d-md-flex m-auto text-center justify-content-center justify-content-md-between">
                <img src="{{asset('images/novupay-logo.png')}}" class="novupay-logo" alt="logo">
            </div>
            <hr>
            <div class="text-center text-uppercase fw-bold">
                Copyright &copy; {{ date('Y') }} | Novupay
            </div>
        </div>
    </div>
</div>
