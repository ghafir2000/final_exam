@extends('web.layout')

@section('title')
    Dr.Pets - Your Chats
@endsection

@section('styles')
<style>
    @if(App::getLocale() == 'ar')
    <style>
        body {
            direction: rtl;
            text-align: right; /* Default for RTL */
        }

        /* --- Chat Index Page Specific RTL --- */

        /* Conversation List Item Active State Border */
        .list-message .list-group-item.active {
            border-right: none !important; /* Remove LTR right border */
            border-left: 3px solid #4CAF50 !important; /* Green left border for RTL */
        }

        /* Panel Heading Pull Left/Right */
        .panel-heading .pull-left { /* Bootstrap 3/custom class */
            float: right !important; /* In RTL, pull-left becomes pull-right visually */
        }
        .list-group-item-heading small.pull-right { /* Bootstrap 3/custom class */
            float: left !important; /* In RTL, pull-right becomes pull-left visually */
        }

        /* clearfix might behave differently, usually fine but check */

        /* Message Alignment in msg_container_base */
        .chat-window .base_sent { /* User's own messages */
            justify-content: flex-start !important; /* Align to the start (right in RTL) */
        }
        .chat-window .base_receive { /* Partner's messages */
            justify-content: flex-end !important; /* Align to the end (left in RTL) */
        }

        /* Avatar Order and Margins */
        .chat-window .base_sent .avatar {
            order: 1 !important; /* Avatar first (right) */
            margin-left: 0 !important;
            margin-right: 10px !important;
        }
        .chat-window .base_sent .messages { /* The message bubble itself */
            order: 2 !important; /* Bubble second (left) */
            float: right !important; /* Ensure bubble aligns to its container's start (right) */
            margin-right: 0 !important; /* was margin-right: 5px */
            margin-left: 5px !important;
        }

        .chat-window .base_receive .avatar {
            order: 2 !important; /* Avatar second (left) */
            margin-right: 0 !important;
            margin-left: 10px !important;
        }
         .chat-window .base_receive .messages { /* The message bubble itself */
            order: 1 !important; /* Bubble first (right) */
            float: left !important; /* Ensure bubble aligns to its container's start (left) */
            margin-left: 0 !important; /* was margin-left: 5px */
            margin-right: 5px !important;
        }

        /* Message Timestamps Alignment */
        .chat-window .messages time {
            text-align: left !important; /* Timestamps typically align to the end of the bubble */
        }

        /* Media Object in Chat Window Header */
        .chat-window .panel-heading .media-left { /* Bootstrap 3/custom class */
            padding-right: 0 !important;
            padding-left: 10px !important; /* Assuming it had padding-right in LTR */
            float: right !important; /* Make avatar appear on the right */
        }
        .chat-window .panel-heading .media-body {
            /* Ensure text aligns correctly if not inheriting body's text-align */
            text-align: right !important;
        }
        .chat-window .panel-heading .media-body .media-heading.ml-2 { /* If ml-2 is BS4/custom */
            margin-left: 0 !important;
            margin-right: 0.5rem !important;
        }


        /* Input Group in Panel Footer */
        .chat-window .panel-footer .input-group {
            /* Flex direction might need to be reversed if button is on wrong side */
            /* By default, input-group-btn might be 'append' which is end. */
        }
        .chat-window .panel-footer .input-group-btn { /* If this is Bootstrap 3 style */
             /* If it's on the right in LTR, it will be on the left (end) in RTL.
                If you want button on the visual left (end), it might be okay.
                If you want button on the visual right (start), you might need to reorder HTML or use flex order.
             */
        }
        /* If using Bootstrap 4/5 input-group structure, it's usually more robust with RTL */
        .chat-window .panel-footer .input-group .form-control {
            /* Text alignment inside input */
             text-align: right;
        }


        /* General layout of col-md-4 and col-md-8 */
        /* Bootstrap's grid should handle RTL correctly by reversing column order.
           .message-sideleft (col-md-4) will appear on the right.
           .message-sideright (col-md-8) will appear on the left.
           If this is not happening, ensure Bootstrap's CSS is loaded correctly and
           that `direction:rtl` is applied high enough in the DOM (e.g., on <body> or <html>).
        */

        /* Avatars within messages:
           You've used `ml-3`, `ml-4`, `mr-3`. These are BS4/custom.
           Need to flip them for RTL.
        */
        .chat-window .msg_container .ml-3 { margin-left: 0 !important; margin-right: 1rem !important; }
        .chat-window .msg_container .ml-4 { margin-left: 0 !important; margin-right: 1.5rem !important; }
        .chat-window .msg_container .mr-3 { margin-right: 0 !important; margin-left: 1rem !important; }


        /* Ensure float directions are correct for messages if you rely on them */
        .chat-window .msg_sent > .messages { /* Your own message */
            float: right !important; /* Should appear on the right (start) */
        }
        .chat-window .msg_receive > .messages { /* Partner's message */
            float: left !important; /* Should appear on the left (end) */
        }

    </style>
@endif

<style>
        /* For loading indicator */
        #message-display-area.loading {
            position: relative; /* Needed for the ::after pseudo-element */
            min-height: 300px; /* Give it some height while loading */
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f9f9f9; /* Optional: light background during load */
        }
        #message-display-area.loading::after {
            content: 'Loading messages...';
            font-size: 1.2em;
            color: #777;
        }
        /* Styles for active item */
        .list-message .list-group-item.active {
            background-color: #87CEEB !important; /* Sky blue */
            border-color: #7AC0D9 !important; /* Darker sky blue */
            border-right: 3px solid #4CAF50 !important; /* Green right border */
        }
        .list-message .list-group-item.active .list-group-item-heading,
        .list-message .list-group-item.active p,
        .list-message .list-group-item.active small {
            color: #ffffff !important;
        }

        /* Placeholder style */
        .chat-placeholder {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100%;
            min-height: 300px; /* Ensure it takes up space */
            text-align: center;
            padding-top: 50px; /* Keep some padding */
        }

        /* Basic Chat Window Styles (add to custom_chat.css for better organization) */
        .chat-window .panel-heading {
            background-color: #f5f5f5;
            border-bottom: 1px solid #ddd;
            padding: 10px 15px;
        }
        .chat-window .chat-partner-name {
            font-weight: bold;
        }
        .chat-window .msg_container_base {
            background: #e5e5e5;
            margin: 0;
            padding: 0 10px 10px;
            max-height: 400px; /* Adjust as needed */
            overflow-x: hidden;
            overflow-y: auto;
        }
        .chat-window .msg_container {
            padding: 5px 0;
            overflow: hidden;
            display: flex;
        }
        .chat-window .base_sent, .chat-window .base_receive {
            display: flex;
            width: 100%;
        }
        .chat-window .base_sent {
            justify-content: flex-end;
            align-items: flex-end;
        }
        .chat-window .base_receive {
            justify-content: flex-start;
            align-items: flex-end;
        }

        .chat-window .messages {
            background: white;
            padding: 10px;
            border-radius: 8px;
            box-shadow: 0 1px 1px rgba(0, 0, 0, .2);
            display: block;
            max-width: 80%; /* Prevent messages from taking full width */
        }
        .chat-window .msg_sent > .messages {
            background: #dcf8c6; /* Light green for sent */
            margin-right: 5px;
        }
        .chat-window .msg_receive > .messages {
            background: #ffffff; /* White for received */
            margin-left: 5px;
        }
        .chat-window .messages p {
            font-size: 0.9em;
            margin: 0 0 0.2rem 0;
            word-wrap: break-word;
        }
        .chat-window .messages time {
            font-size: 0.7em;
            color: #777;
            display: block;
            text-align: right;
        }
        .chat-window .avatar {
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
            width: 40px; /* Fixed width for avatar */
            height: 40px; /* Fixed height for avatar */
            flex-shrink: 0; /* Prevent avatar from shrinking */
        }
        .chat-window .avatar img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 1px solid #ccc;
        }
        .chat-window .base_sent .avatar {
            order: 2; /* Puts avatar to the right for sent messages */
            margin-left: 10px;
        }
        .chat-window .base_receive .avatar {
            order: 1; /* Puts avatar to the left for received messages */
            margin-right: 10px;
        }

        .chat-window .panel-footer {
            padding: 10px 15px;
            background-color: #f5f5f5;
            border-top: 1px solid #ddd;
        }
        .chat-window #message-input { /* This selector was a bit generic, it's better to target within a specific chat window if you have multiple */
            height: auto; /* Allow textarea to grow if needed */
        }
    </style>
@endsection

@section('content')
<div class="container message-container mt-5">
    <div class="row message-wrapper rounded shadow mb-20">
        {{-- Left Side: Conversations List --}}
        <div class="col-md-4 message-sideleft">
            <div class="panel">
                <div class="panel-heading">
                    <div class="pull-left">
                        <h3 class="panel-title">{{ __('Conversations') }}</h3>
                    </div>
                    <div class="clearfix"></div>
                </div><!-- /.panel-heading -->
                <div class="panel-body no-padding">
                    <div class="list-group no-margin list-message" id="conversation-list">
                        @if(isset($selectedChatId) && $selectedChatId)
                            <div id="initial-chat-data-container" data-initial-chat-id="{{ $selectedChatId }}" style="display:none;"></div>
                        @endif
                        @if(isset($chats) && $chats->count() > 0)
                            @foreach($chats as $chat)
                                @php
                                    // Logic for participant name - Default to a translatable string
                                    $participantName = __('Unknown Contact');
                                    $otherParticipant = null;

                                    if ($chat->user_id == Auth::id()) {
                                        $otherParticipant = $chat->chatable;
                                    } elseif ($chat->chatable_id == Auth::id() && $chat->chatable_type === get_class(Auth::user())) {
                                        $otherParticipant = $chat->user;
                                    } else {
                                        // Simplified fallback logic - pick the one that is not the auth user
                                        if ($chat->chatable && (!Auth::check() || $chat->chatable_id != Auth::id() || $chat->chatable_type !== get_class(Auth::user()) )) {
                                            $otherParticipant = $chat->chatable;
                                        } elseif ($chat->user && (!Auth::check() || $chat->user_id != Auth::id())) {
                                            $otherParticipant = $chat->user;
                                        }
                                        // If still null, it might be a chat with oneself or an issue, use a generic name
                                        // or leave as 'Unknown Contact'
                                    }

                                    if ($otherParticipant && isset($otherParticipant->name)) {
                                        $participantName = $otherParticipant->name; // This is dynamic, not translated here
                                    }
                                @endphp

                                <a href="{{ route('chat.open', $chat->id) }}"
                                   data-chat-id="{{ $chat->id }}"
                                   data-chat-url="{{ route('chat.index', ['id' => $chat->id]) }}"
                                   class="list-group-item conversation-link {{ (isset($selectedChatId) && $selectedChatId == $chat->id) ? 'active' : '' }}">
                                    <h4 class="list-group-item-heading">
                                        {{ $participantName }} {{-- Dynamic, not translated here --}}
                                        <small class="pull-right">
                                            @if($chat->latestMessage)
                                                {{ $chat->latestMessage->created_at->diffForHumans() }}
                                            @else
                                                {{ $chat->updated_at->diffForHumans() }}
                                            @endif
                                        </small>
                                    </h4>
                                    <p class="list-group-item-text">
                                        @if($chat->latestMessage)
                                            @if(Auth::check() && $chat->latestMessage->sender_id == Auth::id() && $chat->latestMessage->sender_type == get_class(Auth::user()))
                                                <strong>{{ __('You:') }}</strong>
                                            @endif
                                            {{ Str::limit($chat->latestMessage->message, 40) }} {{-- Dynamic, not translated here --}}
                                        @else
                                            <em>{{ __('No messages yet.') }}</em>
                                        @endif
                                    </p>
                                    <div class="clearfix"></div>
                                </a>
                            @endforeach
                        @else
                            <div class="list-group-item text-center">
                                <p>{{ __('No conversations yet.') }}</p>
                            </div>
                        @endif
                    </div><!-- /.list-group -->
                </div><!-- /.panel-body -->
            </div><!-- /.panel -->
        </div><!-- /.message-sideleft -->

        {{-- Right Side: Message Display Area - This will be dynamically filled --}}
        <div class="col-md-8 message-sideright" id="message-display-area">
            {{-- Placeholder is shown by default, JS will load content or if $selectedChatId is set --}}
            @if(!isset($selectedChatId) || !$selectedChatId) {{-- Only show placeholder if no chat is pre-selected --}}
            <div class="panel chat-placeholder-container">
                <div class="panel-body chat-placeholder">
                    <i class="fa fa-comments-o fa-4x text-muted mb-3"></i>
                    <h4>{{ __('Select a conversation') }}</h4>
                    <p class="text-muted">{{ __('Choose a conversation from the left panel to view messages.') }}</p>
                </div>
            </div>
            @endif
        </div><!-- /.message-sideright -->
    </div>
</div>
@endsection
<script>
    // In your main layout or Blade file where you define window.i18n
    
    console.log("Chat-Index Script: File parsed. Checking for jQuery just before ready(). Available?", (typeof jQuery !== 'undefined' ? jQuery.fn.jquery : 'NO'));
    
    function tryInitChatScript() {
        window.i18n = {
    
            // Add chat-specific translations:
            loadingMessages: "{{ __('Loading messages...') }}",
            unknownSender: "{{ __('Unknown') }}",
            you: "{{ __('You') }}",
            chatPartner: "{{ __('Chat Partner') }}",
            typeYourMessage: "{{ __('Type your message here...') }}",
            send: "{{ __('Send') }}",
            errorInvalidChatData: "{{ __('Error: Received invalid data for chat.') }}",
            failedLoadChat: "{{ __('Failed to load chat. Please try again.') }}",
            selectAConversation: "{{ __('Select a conversation') }}",
            chooseConversationPrompt: "{{ __('Choose a conversation from the left panel to view messages.') }}",
            failedSendMessage: "{{ __('Failed to send message.') }}",
            justNow: "{{ __('Just now') }}"
        };
        console.log("Chat-Index: tryInitChatScript called.");
        if (typeof window.jQuery !== 'undefined') {
            console.log("Chat-Index: jQuery IS NOW DEFINED. Version:", window.jQuery.fn.jquery);


            jQuery(document).ready(function() {
                const messageDisplayArea = $('#message-display-area');
                const conversationList = $('#conversation-list');
                let currentChatId = null;
                let echoChannel = null;


                function escapeHtml(unsafe) {
                    if (unsafe === null || typeof unsafe === 'undefined') {
                        return '';
                    }
                    if (typeof unsafe !== 'string') {
                        return String(unsafe);
                    }
                    return unsafe
                        .replace(/&/g, "&amp;")
                        .replace(/</g, "&lt;")
                        .replace(/>/g, "&gt;")
                        .replace(/"/g, "&quot;")
                        .replace(/'/g, "&#039;");
                }

                function formatTime(isoString) {
                    if (!isoString) return '';
                    const date = new Date(isoString);
                    return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                }

                function scrollToBottom(containerSelector) {
                    const container = $(containerSelector);
                    if (container.length) {
                        // Ensure the container itself is visible before trying to scroll
                        if (container.is(":visible")) {
                            container.scrollTop(container[0].scrollHeight);
                        }
                    }
                }

                
                function renderMessage(message) { // Takes a single message object from openChat's formattedMessages
                    if (!message) {
                        console.error("renderMessage called with null or undefined message object.");
                        return ''; // Return empty string or some error placeholder
                    }

                    // Use the flag directly provided by the openChat controller
                    const isSentByAuthUser = message.is_auth_user_message;

                    const senderAvatar = message.sender_avatar_url;
                    const senderName = escapeHtml(message.sender_name || 'Unknown');
                    const messageText = escapeHtml(message.content || '').replace(/\n/g, "<br />"); // Key is 'content'
                    const messageTime = formatTime(message.created_at_raw);

                    let messageHtml = `
                        <div class="row msg_container ${isSentByAuthUser ? 'base_sent' : 'base_receive'}">`;

                    if (!isSentByAuthUser) {
                        messageHtml += `
                            <div class="col-xs-2 col-md-1 ml-3 avatar">
                                <img src="${senderAvatar}" alt="${senderName}" class="img-responsive img-circle">
                            </div>
                            <div class="col-xs-10 col-md-11  ml-4 mb-2">
                                <div class="messages msg_receive type_msg_text" style="background-color: #e0f7fa; float: left;"> <!-- Light blue -->
                                    <p>${messageText}</p>
                                    <time datetime="${message.created_at_raw}">${senderName} • ${messageTime}</time>
                                </div>
                            </div>`;
                    } else { // Message sent by the authenticated user
                        messageHtml += `
                            <div class="col-xs-10 col-md-11 mr-3 mb-2">
                                <div class="messages msg_sent type_msg_text" style="background-color: #e8f5e9; float: right;"> <!-- Light green -->
                                    <p>${messageText}</p>
                                    <time datetime="${message.created_at_raw}">${messageTime}</time>
                                </div>
                            </div>
                            <div class="col-xs-2 col-md-1 avatar">
                                <img src="${senderAvatar}" alt="You" class="img-responsive img-circle">
                            </div>`;
                    }
                    messageHtml += `</div>`;
                    return messageHtml;
                }

                function renderChatInterface(data) {
                    console.log("Chat-Index: renderChatInterface called. Data:", data);
                    currentChatId = data.chat_id;

                    subscribe(); // Handles Echo subscription

                    const partner = data.chat_partner;
                    // Use translated fallback from window.i18n if defined, otherwise use a hardcoded English default
                    const partnerName = escapeHtml(partner.name || (window.i18n && window.i18n.chatPartner ? window.i18n.chatPartner : 'Chat Partner'));
                    const partnerImage = partner.partner_image;

                    // Get CSRF token from the meta tag
                    const csrfTokenElement = document.querySelector('meta[name="csrf-token"]');
                    const csrfToken = csrfTokenElement ? csrfTokenElement.getAttribute('content') : ''; // Handle if meta tag is missing
                    if (!csrfToken) {
                        console.error('CSRF token not found. Message sending will likely fail.');
                    }

                    // Get translated texts from window.i18n, with fallbacks
                    const placeholderText = (window.i18n && window.i18n.typeYourMessage) ? window.i18n.typeYourMessage : 'Type your message here...';
                    const sendButtonText = (window.i18n && window.i18n.send) ? window.i18n.send : 'Send';

                    const chatWindowHtml = `
                        <div class="panel chat-window" id="chat-window-${data.chat_id}">
                            <div class="panel-heading">
                                <div class="media">
                                    <div class="media-left mb-2">
                                        <img src="${partnerImage}" alt="${partnerName}" class="img-circle" width="30" height="30">
                                    </div>
                                    <div class="media-body">
                                        <h4 class="media-heading chat-partner-name ml-2">${partnerName}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="panel-body msg_container_base" id="messages-list-${data.chat_id}">
                                ${data.messages.map(msg => renderMessage(msg)).join('')}
                            </div>
                            <div class="panel-footer">
                                <form id="send-message-form-${data.chat_id}" data-chat-id="${data.chat_id}">
                                    <input type="hidden" name="_token" value="${csrfToken}">
                                    <div class="input-group">
                                        <input type="text" id="message-input-${data.chat_id}" name="message" class="form-control" placeholder="${placeholderText}" autocomplete="off">
                                        <span class="input-group-btn">
                                            <button class="btn btn-primary" type="submit">${sendButtonText}</button>
                                        </span>
                                    </div>
                                </form>
                            </div>
                        </div>
                    `;

                    // Assuming messageDisplayArea is a jQuery object like $('#message-display-area')
                    messageDisplayArea.html(chatWindowHtml);
                    scrollToBottom(`#messages-list-${data.chat_id}`); // Assuming scrollToBottom is defined

                    // Attach submit event handler
                    $(`#send-message-form-${data.chat_id}`).on('submit', function(e) {
                        e.preventDefault();
                        sendMessage($(this)); // Assuming sendMessage is defined and takes a jQuery form object
                    });
                }

                function loadChat(chatId, chatPageUrl = null, clickedElement = null) {
                    if (currentChatId === chatId && $(`#chat-window-${chatId}`).length > 0) {
                        console.log("Chat already loaded.");
                        if (clickedElement) {
                            conversationList.find('.conversation-link.active').removeClass('active');
                            $(clickedElement).addClass('active');
                        }
                        return;
                    }

                    messageDisplayArea.addClass('loading').html('');

                    $.ajax({
                        url: `{{ url('chat/open') }}/${chatId}`,
                        method: 'GET',
                        dataType: 'json',
                        success: function(response) {
                            messageDisplayArea.removeClass('loading');
                            if (response && response.chat_id) { // Basic check for valid response
                                renderChatInterface(response);

                                conversationList.find('.conversation-link.active').removeClass('active');
                                if (clickedElement) {
                                    $(clickedElement).addClass('active');
                                } else {
                                    conversationList.find(`.conversation-link[data-chat-id="${chatId}"]`).addClass('active');
                                }

                                if (chatPageUrl && window.history && window.history.pushState) {
                                    try {
                                        const currentUrl = new URL(window.location.href);
                                        currentUrl.searchParams.set('chat_id', String(chatId));
                                        history.pushState({ chatId: chatId }, document.title, currentUrl.toString());
                                    } catch (e) {
                                        console.warn("Error constructing URL for history API or invalid chatPageUrl:", e, chatPageUrl);
                                        const fallbackUrl = chatPageUrl || `${window.location.pathname}?chat_id=${chatId}`;
                                        history.pushState({ chatId: chatId }, document.title, fallbackUrl);
                                    }
                                } else {
                                    console.warn("Browser history API not fully supported or chatPageUrl not provided.");
                                }
                            } else {
                                console.error("Invalid response structure from chat/open:", response);
                                messageDisplayArea.html(`<div class="alert alert-danger m-3">Error: Received invalid data for chat.</div>`);
                            }
                        },
                        error: function(xhr, status, error) {
                            messageDisplayArea.removeClass('loading');
                            let errorMsg = 'Failed to load chat. Please try again.';
                            if (xhr.responseJSON && xhr.responseJSON.error) {
                                errorMsg = xhr.responseJSON.error;
                            } else if (xhr.responseText) {
                                try {
                                    const parsedError = JSON.parse(xhr.responseText);
                                    if(parsedError && parsedError.message) {
                                        errorMsg = parsedError.message;
                                    }
                                } catch(e) { /* ignore parsing error, use default */ }
                            }
                            messageDisplayArea.html(`<div class="alert alert-danger m-3">${errorMsg}</div>`);
                            console.error("Error loading chat:", status, error, xhr.responseText);
                        }
                    });
                }

                conversationList.on('click', '.conversation-link', function(e) {
                    e.preventDefault();
                    const chatId = $(this).data('chat-id');
                    const chatPageUrl = $(this).data('chat-url') || $(this).attr('href');
                    loadChat(chatId, chatPageUrl, this);
                    if (echoChannel && currentChatId) {
                        window.Echo.leave('chat.' + currentChatId); echoChannel = null;
                        console.log("unsbscribed to chat :" ,currentChatId );

                    }
                });

                $(window).on('popstate', function(event) {
                    if (event.originalEvent && event.originalEvent.state && event.originalEvent.state.chatId) {
                        const chatId = event.originalEvent.state.chatId;
                        const link = conversationList.find(`.conversation-link[data-chat-id="${chatId}"]`);
                        const chatPageUrl = link.length ? (link.data('chat-url') || link.attr('href')) : null;
                        loadChat(chatId, chatPageUrl, link.length ? link.get(0) : null);
                    } else {
                        currentChatId = null;
                        messageDisplayArea.removeClass('loading').html(`
                            <div class="panel chat-placeholder-container">
                                <div class="panel-body chat-placeholder">
                                    <i class="fa fa-comments-o fa-4x text-muted mb-3"></i>
                                    <h4>Select a conversation</h4>
                                    <p class="text-muted">Choose a conversation from the left panel to view messages.</p>
                                </div>
                            </div>
                        `);
                        conversationList.find('.conversation-link.active').removeClass('active');
                    }
                });

                
                function sendMessage(form) {
                    const chatId = form.data('chat-id');
                    const messageContentInput = form.find('input[name="message"]');
                    const messageContent = messageContentInput.val();
                    const trimmedMessageContent = messageContent.trim();
                    const csrfToken = form.find('input[name="_token"]').val();

                    if (!trimmedMessageContent) {
                        return;
                    }

                    // --- OPTIONAL: Optimistic UI update ---
                    // You could do this here, then potentially replace/update with server response
                    // For simplicity, we'll render from the AJAX success for now.

                    $.ajax({
                        url: `{{ url('chat/message/send') }}`,
                        method: 'POST',
                        data: {
                            _token: csrfToken,
                            chat_id: chatId,
                            message: trimmedMessageContent,
                        },
                        dataType: 'json',
                        success: function(response) {
                            console.log("SendMessage Success Response:", response);
                            messageContentInput.val(''); // Clear input field

                            if (response && response.message_data && response.message_data.message) {
                                
                                const serverMessage = response.message_data; // This is the Eloquent Message model object
                                const picture_url = response.picture_url; // This is the Eloquent Message model object

                                const messageForRenderer = {
                                    id: serverMessage.id, // Good to have
                                    content: serverMessage.message, // Use 'message' key for content
                                    created_at_raw: serverMessage.created_at, // Eloquent 'created_at' is already ISO string
                                    is_auth_user_message: true, // Since the user just sent this
                                    sender_id: serverMessage.sender_id,
                                    sender_type: serverMessage.sender_type,
                                    sender_name: "You", // Or serverMessage.sender.name if you want to be super precise
                                                        // but for "You" it's simpler.
                                    sender_avatar_url: picture_url,
                                                       
                                };

                                // Render the new message and append it to the chat window
                                const newMessageHtml = renderMessage(messageForRenderer);
                                const messagesListContainer = $(`#messages-list-${chatId}`);
                                if (messagesListContainer.length) {
                                    messagesListContainer.append(newMessageHtml);
                                    scrollToBottom(`#messages-list-${chatId}`); // Scroll after appending
                                } else {
                                    console.error(`Could not find #messages-list-${chatId} to append new message.`);
                                }


                                // Update the conversation list snippet on the left
                                const convLink = conversationList.find(`.conversation-link[data-chat-id="${chatId}"]`);
                                if (convLink.length) {
                                    const messageTextFromServer = serverMessage.message; // Already have this
                                    const limitedContent = messageTextFromServer.length > 40 ?
                                                        messageTextFromServer.substring(0, 40) + '...' :
                                                        messageTextFromServer;
                                    convLink.find('.list-group-item-text').html(`<strong>You:</strong> ${escapeHtml(limitedContent)}`);
                                    convLink.find('small.pull-right').text('Just now');
                                }

                            } else {
                                console.warn('Message sent, but response.message_data or its "message" property is missing/invalid.');
                                // Still update timestamp if possible, assuming message was stored
                                const convLink = conversationList.find(`.conversation-link[data-chat-id="${chatId}"]`);
                                if (convLink.length) {
                                    convLink.find('small.pull-right').text('Just now');
                                }
                            }
                        },
                        error: function(xhr) {
                            // ... (error handling same as before) ...
                            let errorMsg = 'Failed to send message.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMsg = xhr.responseJSON.message;
                            } else if (xhr.responseJSON && xhr.responseJSON.error) {
                                errorMsg = xhr.responseJSON.error;
                            }
                            messageContentInput.val(messageContent);
                            alert(errorMsg);
                            console.error("Error sending message:", xhr.responseText, xhr);
                        }
                    });
                }


                // --- INITIAL LOAD (Revised) ---
                const initialChatDataEl = $('#initial-chat-data-container'); // Corrected ID
                if (initialChatDataEl.length && initialChatDataEl.data('initial-chat-id')) {
                    const initialChatId = initialChatDataEl.data('initial-chat-id').toString(); // Ensure string
                    console.log("Attempting to load initial chat from data attribute:", initialChatId);
                    const initialLink = conversationList.find(`.conversation-link[data-chat-id="${initialChatId}"]`);
                    if (initialLink.length) {
                        const initialChatPageUrl = initialLink.data('chat-url') || initialLink.attr('href');
                        loadChat(initialChatId, initialChatPageUrl, initialLink.get(0));
                    } else {
                        console.warn("Initial chat ID from data attribute found, but no matching link in conversation list:", initialChatId);
                        // Optionally, load from URL params as a fallback if data-attr chat not found
                        loadInitialChatFromUrlParams();
                    }
                } else {
                    // No initial chat ID from PHP, try URL params
                    console.log("No initial chat ID from data attribute, trying URL parameters.");
                    loadInitialChatFromUrlParams();
                }

                function loadInitialChatFromUrlParams() {
                    try {
                        const urlParams = new URLSearchParams(window.location.search);
                        const queryChatId = urlParams.get('chat_id');
                        if (queryChatId) { // queryChatId will be a string
                            console.log("Attempting to load initial chat from URL parameter:", queryChatId);
                            // Subscribe to Echo Channel...
                            subscribe();
                            const queryLink = conversationList.find(`.conversation-link[data-chat-id="${queryChatId}"]`);
                            if (queryLink.length) {
                                const queryChatPageUrl = queryLink.data('chat-url') || queryLink.attr('href');
                                loadChat(queryChatId, queryChatPageUrl, queryLink.get(0));
                            } else {
                                console.warn("Chat ID from URL parameter found, but no matching link:", queryChatId);
                            }
                        } else {
                            console.log("No chat_id in URL parameters for initial load.");
                        }
                    } catch (e) {
                        console.warn("Could not parse URL for initial chat ID:", e);
                    }
                }

                function subscribe() {
                    // Subscribe to Echo Channel...
                    if (!echoChannel && currentChatId) {
                        echoChannel = window.Echo.private('chat.' + currentChatId)
                        .listen('NewChatMessage', (e) => {
                                console.log('Pusher e.message object:', JSON.stringify(e.message, null, 2)); // Log the structure of e.message
                                console.log('New message received via Pusher:', e);
                                if (e && e.message) {
                                    // Render the new message and append it to the chat window
                                    const pusherMessage = renderMessage(e.message);
                                    const messagesListContainer = $(`#messages-list-${currentChatId}`);
                                    if (messagesListContainer.length) {
                                        messagesListContainer.append(pusherMessage);
                                        scrollToBottom(`#messages-list-${currentChatId}`); // Scroll after appending
                                    } else {
                                        console.error(`Could not find #messages-list-${currentChatId} to append new message.`);
                                    }
                                }
                            })
                            .error((error) => { /* ... error handling ... */ scrollToBottom(); });
                        console.log('Subscription process started for channel:', 'chat.' + currentChatId);
                    }
                }

            });

        } else {
            console.error("Chat-Index: jQuery still undefined. Retrying in 100ms...");
            setTimeout(tryInitChatScript, 100); // Retry
        }
    }

    // Check if the DOM is already loaded. If so, try immediately.
    // Otherwise, wait for DOMContentLoaded.
    if (document.readyState === "interactive" || document.readyState === "complete") {
        tryInitChatScript();
    } else {
        document.addEventListener('DOMContentLoaded', tryInitChatScript);
    }

</script>



