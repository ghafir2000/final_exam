<html>    {{-- In resources/views/layouts/app.blade.php --}}
<head>
    <link rel="icon" type="image/jpg" href="{{ asset('logos/Dr.pet_logo_transperent.jpg') }}">
    <title>
    @yield('title')
    </title>

    @auth
    <meta name="user-id" content="{{ Auth::user()->id }}">
    @endauth
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

   
    <script>
        // This creates a global JavaScript variable that all your other JS files can use.
        // The `url('/')` helper in Laravel correctly generates the full base URL,
        // including your '/ahmad_ghafeer' subdirectory.
        window.APP_URL = @json(url('/'));
    </script>
    <!-- ======================================================= -->

    {{-- ... meta tags, Vite link for app.css and app.js ... --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- ... Font links, Bootstrap CSS CDN (keep this if you like) ... --}}
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" crossorigin="anonymous">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.5.0/css/all.css" integrity="sha384-B4dIYHKNBt8Bc12p+WXckhzcICo0wtJAoU8YZTY5qE0Id1GSseTk6S+L3BlXeVIU" crossorigin="anonymous">

    <style>
        /* assets/css/custom-app.css or in <style> tag in web.layout */
                body {
                    background-image: url('{{ asset('images/bg.jpg') }}');
                    background-size: 100%;
                    background-position: center;
                }

                .btn-custom-action { /* More generic than just btn-custom */
                    max-height: 39px;
                    /* margin-top: 30px; /* This might be too much for inline buttons, use utility classes instead */
                    /* margin-left: 10px; /* Use utility classes like ms-2 */
                }

                .card-header.bg-warning h2, .card-header.bg-warning h3 {
                    margin-bottom: 0; /* Remove default margin from h2 if any */
                }

                .media-preview img {
                    max-width: 200px;
                    max-height: 200px;
                    object-fit: cover;
                    border: 1px solid #ddd;
                    border-radius: .25rem;
                    margin-bottom: 10px;
                }

                .transparent-edit-btn {
                    background-color: transparent !important;
                    border: none !important;
                    color: #ffc107; /* Bootstrap warning color */
                    padding: 0.25rem 0.5rem;
                }
                .transparent-edit-btn:hover {
                    color: #e0a800; /* Darker warning */
                }

                .comment-avatar {
                    width: 40px;
                    height: 40px;
                }
        </style>
        @if(App::getLocale() == 'ar')
            <style>
                body {
                    direction: rtl;
                    text-align: right;
                }

                /* --- Navbar Adjustments (Keep your existing good rules) --- */
                .main-navbar .navbar-nav.ml-auto { margin-left: 0 !important; margin-right: auto !important; }
                .main-navbar .mr-1 { margin-left: 0.25rem !important; margin-right: 0 !important; }
                .main-navbar #navbarDropdownNotifications .badge-pill { right: auto !important; left: 2px !important; }
                .main-navbar #notification-dropdown-menu { left: auto !important; right: 0 !important; }


                /* --- Profile Sidebar Adjustments for RTL --- */
                .profile-sidebar {
                    /* Base fixed position (assuming LTR default is 'right: -WIDTHpx;') */
                    position: fixed;
                    top: 0;
                    /* For RTL, we control it from the 'left' (which is the 'end' side) */
                    right: auto !important; /* Nullify LTR 'right' positioning */
                    left: -300px;  /* Start off-screen to the VISUAL LEFT (width of sidebar) */
                    width: 300px; /* Define your sidebar width */
                    height: 100%;
                    background-color: #fff; /* Example background */
                    z-index: 1050; /* Ensure it's above other content */
                    transition: left 0.3s ease-in-out !important; /* Animate the 'left' property for RTL slide */
                }
                .profile-sidebar.show {
                    left: 0 !important; /* Slide in from the VISUAL LEFT */
                    right: auto !important;
                }

                /* Close button on the visual left (end) of the sidebar header */
                .profile-sidebar .close-sidebar-btn {
                    /* Adjust positioning as needed, e.g., if header is flex */
                    margin-left: auto; /* Pushes it to the end (left) if header is flex and items are start-aligned */
                    /* Or absolute/float if simpler */
                    /* float: left; */
                }


                /* For the inline style: margin-right: 90px; */
                .profile-sidebar span.sidebar-username[style*="margin-right: 90px"] {
                    margin-right: 0 !important; /* Clear LTR inline style */
                    margin-left: 90px !important; /* Apply to the 'end' (visual left) */
                }


                /* Example of forcing a BS4 .mr-1 to act as margin-start in RTL */
                .profile-sidebar .sidebar-link i.mr-1 {
                    margin-right: 0 !important;
                    margin-left: 0.25rem !important;
                }


                /* Form Switch in RTL */
                .profile-sidebar .form-check.form-switch {
                    padding-left: 2.5em; /* Original Bootstrap LTR padding-left for the switch track */
                    padding-right: 0;
                }
                .profile-sidebar .form-check-input {
                    margin-left: -1.7em; /* Original Bootstrap LTR margin-right to pull input into track */
                    margin-right: 0;
                    float: right; /* Make the switch itself appear on the right in RTL */
                }



                /* Your generic BS4 flips (ensure these don't conflict with more specific ones above) */
                /* These are broad. Be careful. */
                /* .mr-auto { margin-right: 0 !important; margin-left: auto !important; } */
                /* .ml-1 { margin-left: 0 !important; margin-right: 0.25rem !important; } */

                .text-center { text-align: center !important; }

            </style>
            @endif
            
            @yield('styles')
            </head>
        
    <body>
        @include('web.navbar')
        <main>
            @yield('content')
        </main>

        <script>
        document.addEventListener('DOMContentLoaded', function () {
            const pageKey = 'scrollPos_{{ Route::currentRouteName() ?? "default" }}'; // Unique key for sessionStorage

            // Function to save scroll position
            function saveScrollPosition() {
                sessionStorage.setItem(pageKey, window.scrollY);
            }

            // Try to scroll to a URL fragment first (e.g., #booking-row-123)
            if (window.location.hash) {
                const elementToScrollTo = document.querySelector(window.location.hash);
                if (elementToScrollTo) {
                    // Timeout helps ensure the element is fully rendered and positioned
                    setTimeout(() => {
                        elementToScrollTo.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        // Optional: Add a temporary highlight
                        elementToScrollTo.classList.add('highlight-row');
                        setTimeout(() => {
                            elementToScrollTo.classList.remove('highlight-row');
                        }, 3000); // Remove highlight after 3 seconds
                    }, 100); // Adjust timeout if needed
                    return; // Don't restore general scroll position if we scrolled to a fragment
                }
            }

            // If no fragment, restore general scroll position
            const savedScrollPosition = sessionStorage.getItem(pageKey);
            if (savedScrollPosition) {
                // Timeout can also be useful here if content loads dynamically
                setTimeout(() => {
                    window.scrollTo(0, parseInt(savedScrollPosition, 10));
                    sessionStorage.removeItem(pageKey); // Clear after use
                }, 50);
            }

            // --- Event listeners to save scroll position before navigation ---

            // For form submissions (filters, booking updates, cancellations)
            const forms = document.querySelectorAll('form'); // Or be more specific: 'form.scroll-submit-form', 'form[method="GET"]'
            forms.forEach(form => {
                form.addEventListener('submit', saveScrollPosition);
            });

            // For pagination links
            const paginationLinks = document.querySelectorAll('.pagination a');
            paginationLinks.forEach(link => {
                link.addEventListener('click', saveScrollPosition);
            });

            // For other links that might cause a reload on the same page
            // (Adjust selector if needed, e.g., specific view links if they reload the index)
            // const otherNavLinks = document.querySelectorAll('a.some-class');
            // otherNavLinks.forEach(link => {
            //     link.addEventListener('click', saveScrollPosition);
            // });
        });
        </script>

        {{-- All page-specific scripts that might use jQuery go here --}}
        @yield('scripts')

        {{-- Includes that might also use jQuery --}}
        @auth
            @include('web.AI-chat-widget')
        @endauth
        @auth
            @include('web.chats-button')
        @endauth
    </body>
</html>