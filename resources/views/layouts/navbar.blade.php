<nav class="navbar navbar-expand-lg">
    <div class="container">
        <a class="navbar-brand">
            <img src="{{asset('images/novupay-logo.png')}}" alt="novupay">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarScroll" aria-controls="navbarScroll" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarScroll">
            <ul class="navbar-nav ms-auto my-2 gap-5 my-lg-0">
                <li class="nav-item">
                    <a href="#payment-partners" class="nav-link active" aria-current="page">Payment Partners</a>
                </li>
                <li class="nav-item">
                    <a href="#business-solutions" class="nav-link active" aria-current="page">Business Solutions</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        API Docs
                    </a>
                    {{-- <ul class="dropdown-menu">
                        <li>
                            <a target="_blank" class="dropdown-item" href="{{ route('payment.merchants') }}" aria-current="page">Documentation</a>
                        </li>
                        <li>
                            <a target="_blank" class="dropdown-item" href="{{ route('payment.merchants') }}" aria-current="page">Payment Demo</a>
                        </li>
                        <li>
                            <a target="_blank" class="dropdown-item" href="{{route('home.status', ['status' => 'success'])}}">Successful Payment</a>
                        </li>
                        <li>
                            <a target="_blank" class="dropdown-item" href="{{route('home.status', ['status' => 'error'])}}">Error Payment</a>
                        </li>
                    </ul> --}}
                </li>
                <li class="nav-item">
                    <a target="_blank" href="https://novulutions.com" class="nav-link active" aria-current="page">About Us</a>
                </li>
            </ul>
        </div>
    </div>
</nav>