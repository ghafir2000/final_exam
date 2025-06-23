{{-- This will contain the chat button and the chat window --}}
<div id="chat-widget-container">
    {{-- Floating Chat Button --}}
    <button id="chat-toggle-btn" class="btn btn-success rounded-circle shadow-sm">
        <i class="fas fa-robot"></i>
    </button>

    @php
        $user = App\Models\User::find(Auth::id()); // Get the authenticated user directly
    @endphp

    {{-- Chat Window (initially hidden) - Tailored for AI Chat --}}
    <div id="chat-window" class="card chat-window-card" style="display: none; width: 600px;">
        <div class="card-header msg_head">
            <div class="d-flex bd-highlight">
                <div class="img_cont">
                    <img src="" class="rounded-circle user_img" id="chat-partner-img" alt="{{ __('AI Image') }}">
                </div>
                <div class="user_info mr-auto">
                    <span id="chat-partner-name">{{ __('Loading AI Chat...') }}</span>
                    <p id="chat-message-count"></p>
                </div>
                <div class="card-header-actions mt-3">
                    <span id="user_info"></span>
                    <button type="button" class="btn btn-sm btn-danger" id="clear-chat-btn"><i class="fas fa-trash-alt"></i> {{ __('Clear Chat') }}</button>
                </div>
            </div>
        </div>

        {{-- Message Body - Where AI and User messages appear --}}
        <div class="card-body msg_card_body" id="message-body">
             {{-- Messages will be appended here by JavaScript --}}
        </div>

        <div class="card-footer">
            <div class="input-group">
                <textarea name="message" id="chat-message-input" class="form-control type_msg" placeholder="{{ __('Loading chat...') }}"></textarea>
                <div class="input-group-append">
                    <span class="input-group-text send_btn" id="send-message-btn"><i class="fas fa-location-arrow"></i></span>
                </div>
            </div>
        </div>
    </div>
</div>
@auth
    @php
        $loggedInUser = $user;
        $userProfilePicSpatieRaw = $loggedInUser ? $loggedInUser->getFirstMediaUrl('profile_picture') : 'USER_HAS_NO_SPATIE_MEDIA';
        $userProfilePicSpatieAsset = $loggedInUser ? asset($loggedInUser->getFirstMediaUrl('profile_picture')) : 'USER_HAS_NO_SPATIE_MEDIA_ASSET_WRAPPED';
        $defaultUserPic = asset('images/default_user_avatar.png');
    @endphp
<!-- 
    DEBUGGING OUTPUT - View this in your browser's "View Source" 
    <p>DEBUG: LoggedInUser ID: {{ $loggedInUser ? $loggedInUser->id : 'N/A' }}</p>
    <p>DEBUG: Spatie Raw URL: {{ $userProfilePicSpatieRaw }}</p>
    <p>DEBUG: Spatie Asset-Wrapped URL: {{ $userProfilePicSpatieAsset }}</p>
    <p>DEBUG: Default User Pic URL: {{ $defaultUserPic }}</p>
    END DEBUGGING OUTPUT -->

    <input type="hidden" id="current-user-id" value="{{ Auth::id() }}">
    <input type="hidden" id="current-user-img-url" value="{{ $loggedInUser->getFirstMediaUrl('profile_picture') ?: asset('images/default_user_avatar.png') }}">
    @endauth

<!-- CSS for Styling and Animation -->
<style>
    #chat-floating-btn-container {
        position: fixed;
        bottom: 20px;
        left: 20px;
        z-index: 700;
    }

    #chat-toggle-btn {
        width: 50px;
        height: 50px;
        display: flex;
        justify-content: center;
        align-items: center;
        background-color:rgb(6, 88, 17);
        transition: transform 0.3s ease;
    }

    #chat-toggle-btn:hover {
        transform: scale(1.1);
    }
</style>

{{-- Include necessary CSS and JS --}}
{{-- Assuming these are included correctly in your layout --}}
@push('styles')
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" crossorigin="anonymous">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.5.0/css/all.css" integrity="sha384-B4dIYHKNBt8Bc12p+WXckhzcICo0wtJAoU8YZTY5qE0Id1GSseTk6S+L3BlXeVIU" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/malihu-custom-scrollbar-plugin/3.1.5/jquery.mCustomScrollbar.min.css">
    <link rel="stylesheet" href="{{ asset('css/AI-chat.css') }}"> {{-- Your custom/overridden chat styles --}}
@endpush

@push('scripts')
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/malihu-custom-scrollbar-plugin/3.1.5/jquery.mCustomScrollbar.min.js"></script>
    {{-- Make sure your refactored chat.js is included AFTER these dependencies --}}
    {{-- <script src="{{ asset('js/AI-chat.js') }}"></script> --}}
@endpush

