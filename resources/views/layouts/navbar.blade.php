<nav class="navbar navbar-expand-lg">
    <div class="container">
        <a class="navbar-brand" href="{{ route('home') }}">
            <img src="{{ asset('images/novupay-logo.png') }}?v=2" alt="novupay">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarScroll" aria-controls="navbarScroll" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarScroll">
            <ul class="navbar-nav ms-auto my-2 gap-5 my-lg-0">
                <li class="nav-item">
                    <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" aria-current="page">
                        Home
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('home') }}#business-solutions" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">
                        Business Solutions
                    </a>
                </li>
                @if(config('novupay.other_services_enabled'))
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle {{ request()->routeIs('services.novustream.whitepaper') || request()->routeIs('services.novusurge.whitepaper') || request()->routeIs('services.novusurge.meter-inspection.whitepaper') ? 'active' : '' }}" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Other Services
                    </a>
                    <ul class="dropdown-menu">
                        <li>
                            <h6 class="dropdown-header">NovuStream</h6>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('services.novustream.whitepaper') }}">White Paper</a>
                        </li>
                        <li>
                            <h6 class="dropdown-header">NovuSurge</h6>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('services.novusurge.whitepaper') }}">Electric utilities white paper</a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('services.novusurge.meter-inspection.whitepaper') }}">Meter inspection white paper</a>
                        </li>
                    </ul>
                </li>
                @endif
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle {{ request()->routeIs('api.docs') || request()->routeIs('services.novupay.whitepaper') || request()->routeIs('services.novupay.processflow') ? 'active' : '' }}" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        API Docs
                    </a>
                    <ul class="dropdown-menu">
                        <li>
                            <a class="dropdown-item" href="{{ route('api.docs') }}">Documentation</a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('services.novupay.whitepaper') }}">OPS White Paper</a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('services.novupay.processflow') }}">OPS Process Flow</a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('api.docs') }}#integration">Integration Guide</a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('api.docs') }}#examples">Code Examples</a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a href="{{ route('about') }}" class="nav-link {{ request()->routeIs('about') ? 'active' : '' }}">
                        About
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('demo') }}" class="nav-link {{ request()->routeIs('demo') ? 'active' : '' }}">
                        Demo
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('contact') }}" class="nav-link {{ request()->routeIs('contact') ? 'active' : '' }}">
                        Contact
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>