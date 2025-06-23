<!-- navbar.blade.php (Bootstrap 4 - Bell on Left) -->
<nav class="navbar navbar-expand-lg navbar-light bg-success main-navbar fixed-top">
    <div class="container-fluid">
        {{-- Left-aligned items --}}
        <a class="navbar-brand" href="{{ route('home') }}">
            <img src="{{ asset('logos/Dr.pet_logo_transperent.jpg') }}" width="50" height="50" alt="{{ __('Dr.Pet logo') }}" class="d-inline-block align-top">
        </a>

        {{-- Navbar Toggler Button remains here for mobile --}}
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavContent"
            aria-controls="navbarNavContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNavContent">
            {{-- Section 1: Items pushed to the left (Notification Bell) - REVERTED TO ORIGINAL --}}
            <ul class="navbar-nav align-items-center">
                @auth
                <li class="nav-item dropdown">
                    <a id="navbarDropdownNotifications"
                       class="nav-link dropdown-toggle"
                       href="#"
                       role="button"
                       data-toggle="dropdown"
                       aria-haspopup="true"
                       aria-expanded="false"
                       style="position: relative;">
                        <i class="fas fa-bell"></i>
                        <span class="badge badge-danger badge-pill" id="notification-count"
                              style="position: absolute; top: 2px; right: 2px; font-size: 0.65em; line-height: 1; padding: 0.2em 0.45em; display: none;">
                        </span>
                    </a>
                    <div class="dropdown-menu"
                         aria-labelledby="navbarDropdownNotifications"
                         id="notification-dropdown-menu">
                        <span class="dropdown-item text-notification text-center small">{{ __('Loading notifications...') }}</span>
                    </div>
                </li>
                @endauth
            </ul>

            {{-- Section 2: Items pushed to the right (Main Navigation and User Links) --}}
            <ul class="navbar-nav align-items-center ml-auto">
                <li class="nav-item">
                    <a class="nav-link {{ Request::routeIs('blog.index') || Request::routeIs('blog.index') ? 'active' : '' }}" href="{{ route('blog.index') }}">
                        <img src="{{ asset('logos/home_logo.jpg') }}" width="25" height="25" alt="{{ __('Home Icon') }}" class="d-inline-block align-middle mr-1"> {{ __('Home') }}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ Request::routeIs('animal.index') ? 'active' : '' }}" href="{{ route('animal.index') }}">
                        <img src="{{ asset('logos/animals_logo.jpg') }}" width="35" height="35" alt="{{ __('Animals Icon') }}" class="d-inline-block align-middle mr-1"> {{ __('Animals') }}
                    </a>
                </li>
                {{-- ... other main nav items ... --}}
                <li class="nav-item">
                    <a class="nav-link {{ Request::routeIs('product.index') ? 'active' : '' }}" href="{{ route('product.index') }}">
                        <img src="{{ asset('logos/products_logo.jpg') }}" width="25" height="25" alt="{{ __('Product Icon') }}" class="d-inline-block align-middle mr-1"> {{ __('Products') }}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ Request::routeIs('service.index') ? 'active' : '' }}" href="{{ route('service.index') }}">
                        <img src="{{ asset('logos/partner_logo.jpg') }}" width="35" height="35" alt="{{ __('Service Icon') }}" class="d-inline-block align-middle mr-1"> {{ __('Services') }}
                    </a>
                </li>

                @auth
                    @if (!Auth::user()->hasRole('provider'))
                    <li class="nav-item">
                        <a class="nav-link {{ Request::routeIs('cart.index') ? 'active' : '' }}" href="{{ route('cart.index') }}">
                            <img src="{{ asset('logos/cart_logo2.jpg') }}" width="30" height="30" alt="{{ __('Cart Icon') }}" class="d-inline-block align-middle mr-1"> {{ __('Cart') }}
                        </a>
                    </li>
                    @endif
                    <li class="nav-item">
                        <a class="nav-link profile-sidebar-toggle" href="#" id="profile-sidebar-trigger">
                            <img class="rounded-circle" width="30px" src= "{{ Auth()->user()->getFirstMediaUrl('profile_picture') ?: asset('images/upload_default.jpg') }}" alt="{{ Auth::user()->name }}'s {{ __('profile picture') }}">
                            {{ Auth::user()->name }}
                        </a>
                    </li>
                @else
                    <li class="nav-item">
                        <button type="button" class="btn btn-sm btn-outline-light" onclick="window.location.href='{{ route('login') }}'">
                            {{ __('Login') }}
                        </button>
                    </li>
                @endauth
            </ul>
        </div>
    </div>
</nav>

@auth
    @php
        $user = Auth::user();
        $availableLocales = Config::get('app.available_locales', ['en' => 'English', 'es' => 'Español']);
        $currentLocale = App::getLocale();
    @endphp

    <div id="profile-sidebar" class="profile-sidebar">
        <div class="profile-sidebar-header">
            <a href="{{ route('profile') }}" class="sidebar-username d-flex align-items-center text-decoration-none">
                <img class="rounded-circle" width="50px" src="{{ $user->getFirstMediaUrl('profile_picture') ?: asset('images/upload_default.jpg') }}" alt="{{ $user->name }}'s {{ __('profile picture') }}">
                <span class="sidebar-username ms-2" style="font-weight: bold; margin-right: 90px;">{{ $user->name }}</span>
            </a>
            <button id="close-sidebar-btn" class="close-sidebar-btn">×</button>
        </div>

        <div class="profile-sidebar-content">
            <a href="{{ route('user.edit') }}" class="sidebar-link btn"><i class="fas fa-user me-2"></i> {{ __('Edit Profile') }}</a>
            
            @if($user->userable_type == "App\Models\Customer")
            <br>
                <a href="{{ route('pet.index') }}" class="sidebar-link btn"><i class="fas fa-paw me-2"></i> {{ __('My Pets') }}</a>
                <a href="{{ route('pet.create') }}" class="sidebar-link w-100 text-start btn btn-link p-0 border-0"><i class="fas fa-plus me-2"></i> {{ __('Add Pet') }}</a>
                <form action="{{ route('booking.create') }}" method="GET" class="sidebar-form">
                    <input type="hidden" name="start" value="1">
                    <button type="submit" class="sidebar-link w-100 text-start btn btn-link p-0 border-0"><i class="fas fa-calendar-plus me-2"></i> {{ __('Make a Booking') }}</button>
                </form>
                <a href="{{ route('order.index') }}" class="sidebar-link btn"><i class="fas fa-shopping-cart me-2"></i> {{ __('My Orders') }}</a>
            @endif

            @if($user->userable_type == "App\Models\Veterinarian" || $user->userable_type == "App\Models\Partner")
            <br>
                <a href="{{ route('booking.index') }}" class="sidebar-link btn"><i class="fas fa-calendar-alt me-2"></i> {{ __('My Bookings') }}</a>
                <form action="{{ route('service.index') }}" method="GET" class="sidebar-form">
                    @csrf
                    <input type="hidden" name="servicable_id" value="{{ $user->userable_id }}">
                    <input type="hidden" name="servicable_type" value="{{ $user->userable_type }}">
                    <button type="submit" class="sidebar-link btn"><i class="fas fa-clipboard me-2"></i> {{ __('My Services') }}</button>
                </form>
                <a href="{{ route('service.create') }}" class="sidebar-link w-100 text-start btn btn-link p-0 border-0"><i class="fas fa-plus me-2"></i> {{ __('New Service') }}</a>
                <form action="{{ route('product.index') }}" method="GET" class="sidebar-form">
                    @csrf
                    <input type="hidden" name="productable_id" value="{{ $user->userable_id }}">
                    <input type="hidden" name="productable_type" value="{{ $user->userable_type }}">
                    <button type="submit" class="sidebar-link btn"><i class="fas fa-box-open me-2"></i> {{ __('My Products') }}</button>
                </form>
                <a href="{{ route('product.create') }}" class="sidebar-link w-100 text-start btn btn-link p-0 border-0"><i class="fas fa-plus me-2"></i> {{ __('New Product') }}</a>
            @endif

            @canany(['edit media', 'edit users'])
                <hr class="sidebar-divider">
                <h6 class="sidebar-heading">{{ __('Admin Actions') }}</h6>
                @can('edit media')
                    <a href="{{ route('animal.create') }}" class="sidebar-link"><i class="fas fa-bone me-2"></i> {{ __('Add Animal Type') }}</a>
                @endcan
                @can('edit users')
                    <a href="{{ route('admin.index', ['roles' => ''])}}" class="sidebar-link"><i class="fas fa-users me-2"></i> {{ __('All Users') }}</a>
                    <a href="{{ route('admin.create') }}" class="sidebar-link"><i class="fas fa-user-plus me-2"></i> {{ __('Make New Admin') }}</a>
                @endcan
            @endcanany
        </div>

        <div class="profile-sidebar-footer">
            <div class="sidebar-setting-item">
                <label for="language-switcher"><i class="fas fa-language me-2"></i> {{ __('Language:') }}</label>
                <select id="language-switcher" class="form-control form-control-sm " style="width: 150px;">
                    @foreach ($availableLocales as $locale => $label)
                        <option value="{{ $locale }}" {{ $locale == $currentLocale ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="sidebar-setting-item">
                <span><i class="fas fa-moon me-2"></i> {{ __('Dark Mode:') }}</span>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="dark-mode-checkbox" {{ session('darkMode', false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="dark-mode-checkbox"></label>
                </div>
            </div>
                    
            <a href="#" id="logout-sidebar-link" class="sidebar-link logout-link"><i class="fas fa-sign-out-alt me-2"></i> {{ __('Logout') }}</a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">@csrf</form>
        </div>
    </div>
    <div id="sidebar-overlay" class="sidebar-overlay"></div>
@endauth

<script>
    window.i18n = {
        noNewNotifications: "{{ __('No new notifications') }}",
        allNotifications: "{{ __('View all notifications') }}"
    };

    document.addEventListener('DOMContentLoaded', function () {
        const sidebar = document.getElementById('profile-sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        const trigger = document.getElementById('profile-sidebar-trigger');
        const closeBtn = document.getElementById('close-sidebar-btn');

        if (trigger && sidebar && overlay) {
            trigger.addEventListener('click', function (e) {
                e.preventDefault();
                sidebar.classList.add('show');
                overlay.classList.add('show');
            });
        }
        if (closeBtn && sidebar && overlay) {
            closeBtn.addEventListener('click', function () {
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
            });
        }
        if (overlay && sidebar) {
            overlay.addEventListener('click', function () {
                 sidebar.classList.remove('show');
                 overlay.classList.remove('show');
            });
        }
        if (sidebar) {
             sidebar.addEventListener('click', function(event) { event.stopPropagation(); });
        }

        const logoutLink = document.getElementById('logout-sidebar-link');
        const logoutForm = document.getElementById('logout-form');
        if (logoutLink && logoutForm) {
             logoutLink.addEventListener('click', function(event) {
                 event.preventDefault();
                 logoutForm.submit();
             });
         }

        const darkModeCheckbox = document.getElementById('dark-mode-checkbox');
        if (darkModeCheckbox) {
             const isDarkMode = localStorage.getItem('darkMode') === 'true';
             darkModeCheckbox.checked = isDarkMode;
             document.body.classList.toggle('dark-mode', isDarkMode);
             darkModeCheckbox.addEventListener('change', function() {
                 document.body.classList.toggle('dark-mode', this.checked);
                 localStorage.setItem('darkMode', this.checked ? 'true' : 'false');
             });
        }

         const languageSwitcher = document.getElementById('language-switcher');
         if (languageSwitcher) {
             languageSwitcher.addEventListener('change', function() {
                 window.location.href = window.APP_URL + '/lang/' + this.value;
             });
         }

        const mainNavbar = document.querySelector('.main-navbar');
        let lastScrollTopForNavbar = 0;
        const scrollThresholdForNavbar = 50;
        const hideShowThresholdForNavbar = 5;

        if (mainNavbar) {
            function handleNavbarScroll() {
                let currentScrollPos = window.pageYOffset || document.documentElement.scrollTop;
                if (currentScrollPos > scrollThresholdForNavbar) { mainNavbar.classList.add('scrolled'); } else { mainNavbar.classList.remove('scrolled'); }

                if (currentScrollPos > lastScrollTopForNavbar && currentScrollPos > (mainNavbar.offsetHeight || 70)) {
                    if (Math.abs(currentScrollPos - lastScrollTopForNavbar) > hideShowThresholdForNavbar) { mainNavbar.classList.add('navbar-hidden'); }
                } else {
                    if (Math.abs(currentScrollPos - lastScrollTopForNavbar) > hideShowThresholdForNavbar || currentScrollPos < (mainNavbar.offsetHeight || 70)) { mainNavbar.classList.remove('navbar-hidden'); }
                }
                lastScrollTopForNavbar = currentScrollPos <= 0 ? 0 : currentScrollPos;
            }
            handleNavbarScroll();
            window.addEventListener('scroll', handleNavbarScroll, false);
        }
    });
</script>
