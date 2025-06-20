<title>{{ __('Dr.Pets - Payment Result') }}</title>

<style>
    body {
        padding-top: 0 !important;
    }
    .fade-in {
        opacity: 0;
        transition: opacity 2s ease-in-out;
    }

    .fade-in.appear {
        opacity: 1;
    }
    .drpet-green {
        color: #34C759;
    }
    .drpet-yellow {
        color: #F7DC6F;
    }
    .container {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 90vh;
    }
    .profile-btn {
        background-color: #34C759;
        border: none;
        color: white;
        padding: 5px 3px;
        text-decoration: none;
        font-size: 16px;
        font-weight: bold;
        margin-left: 90px;
        cursor: pointer;
        border-radius: 5px;
    }
</style>

<body>
    <div class="container">
        <div class="text-center">
        @if ($payment->status == \App\Enums\PaymentEnums::SUCCESS)
            <h1 id="welcome" class="fade-in drpet-green">{{ __(':type Successful', ['type' => class_basename($payment->payable_type)]) }}</h1>
        @else
            <h1 id="welcome" class="fade-in drpet-yellow">{{ __(':type Canceled', ['type' => class_basename($payment->payable_type)]) }}</h1>
        @endif

            <a id="profileButton" href="{{ route('profile') }}" class="profile-btn mt-3 fade-in">{{ __('Profile') }}</a>
            <script>
                window.addEventListener('load', () => {
                    const welcome = document.getElementById('welcome');
                    const profileButton = document.getElementById('profileButton');

                    welcome.classList.add('appear');

                    setTimeout(() => {
                        profileButton.classList.add('appear');
                    }, 2000);
                });
            </script>
        </div>
    </div>
</body>

</html>

