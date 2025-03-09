<nav class="navbar navbar-expand-lg">
    <div class="container">
        <a class="navbar-brand" href="#">
            <img src="{{asset('images/novupay-logo.png')}}" alt="novupay">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarScroll" aria-controls="navbarScroll" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarScroll">
            <ul class="navbar-nav ms-auto my-2 gap-5 my-lg-0">
                <li class="nav-item">
                    <a href="#partners" class="nav-link active" aria-current="page" href="#">Payment Partners</a>
                </li>
                <li class="nav-item">
                    <a href="#about-us" class="nav-link active" aria-current="page" href="#">Business Solutions</a>
                </li>
                <li class="nav-item">
                    <a href="" class="nav-link active" aria-current="page" href="#">Api Documentation</a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('payment.demo') }}" class="nav-link active" aria-current="page" href="#">Demo</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Page Statuses
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{route('home.status', ['status' => 'success'])}}">Successful Payment</a></li>
                        <li><a class="dropdown-item" href="{{route('home.status', ['status' => 'error'])}}">Error Payment</a></li>
                        <li><a class="dropdown-item" href="{{route('home.status', ['status' => 'pending'])}}">Pending Payment</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>