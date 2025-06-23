import SimpleBar from 'simplebar'; // Import SimpleBar

$(document).ready(function() {

    // --- Cache jQuery Selectors ---
    const chatToggleBtn = $('#chat-toggle-btn');
    const chatWindow = $('#chat-window');
    const messageBody = $('#message-body'); // The element SimpleBar is attached to
    const messageBodyEl = document.getElementById('message-body'); // Get the raw DOM element for SimpleBar

    const messageInput = $('#chat-message-input');
    const sendMessageBtn = $('#send-message-btn');
    const actionMenuBtn = $('#action_menu_btn'); // Assuming this button still exists if not removed
    const actionMenu = $('.action_menu'); // Assuming this menu still exists if not removed
    const chatPartnerNameEl = $('#chat-partner-name');
    const chatPartnerImgEl = $('#chat-partner-img');
    const messageCountEl = $('#chat-message-count');

    const clearChatBtn = $('#clear-chat-btn');

    const currentUserId = $('#current-user-id').val();
    const currentUserImgUrl = $('#current-user-img-url').val(); // Ensure fallback
    console.log("Initial currentUserImgUrl:", currentUserImgUrl);

    let currentChatId = $('#current-chat-id').val();
    let simpleBarInstance = null;
    let echoChannel = null;
    let isScrolling = false; // Flag to prevent rapid scroll calls


    // --- Initialize SimpleBar ---
    // Ensure SimpleBar is only initialized once and only if the element exists
    if (messageBodyEl && !messageBodyEl.classList.contains('simplebar-initialized')) { // Check if element exists and SimpleBar isn't already attached
        simpleBarInstance = new SimpleBar(messageBodyEl, {
            autoHide: true,
            // You can add other SimpleBar options here if needed
            // e.g., forceVisible: 'y',
        });
        // Add a class to mark it as initialized to prevent re-initialization
        messageBodyEl.classList.add('simplebar-initialized');
        console.log('SimpleBar initialized.');
    } else if (messageBodyEl && messageBodyEl.classList.contains('simplebar-initialized')) {
         // If already initialized, get the existing instance if needed elsewhere
         // SimpleBar doesn't expose a global getInstance easily, but you can often
         // find it attached to the element's data or a global map if needed.
         // For this code's scope, the `simpleBarInstance` variable is sufficient
         // if the script only runs once. If the script could run multiple times
         // on the same element, a more robust check or re-get might be needed.
         console.log('SimpleBar already initialized on #message-body.');
         // If you need to get the instance later, you might need a workaround
         // or restructure. For this script's assumed single execution, the variable is fine.
    }
     else {
        console.error('#message-body element not found for SimpleBar initialization.');
    }


    // --- Utility Functions ---
    function formatTime(timestamp) {
        if (!timestamp) return '';
        const date = new Date(timestamp);
        if (isNaN(date.getTime())) return '';
        const hours = date.getHours().toString().padStart(2, '0');
        const minutes = date.getMinutes().toString().padStart(2, '0');
        return `${hours}:${minutes}`;
    }

    // --- Core Rendering Function ---
    function renderMessage(message) {
        // Validation
        if (!message || !message.user || typeof message.user.id === 'undefined' || typeof message.user.type === 'undefined') {
            console.error("renderMessage FAILED: Invalid message.user structure. Message data:", JSON.stringify(message));
            return; // Don't append invalid messages
        }

        const messageUser = message.user;
        const isMyMessage = (String(messageUser.id) === String(currentUserId) && messageUser.type === 'App\\Models\\User');

        let determinedProfileImageUrl;
        let senderNameForDisplay;
        let senderNameForAlt;

        if (isMyMessage) {
            determinedProfileImageUrl = currentUserImgUrl;
            senderNameForDisplay = "You";
            senderNameForAlt = messageUser.name || 'You';
        } else { // AI's message
            determinedProfileImageUrl = messageUser.profile_picture || messageUser.image_url || "images/veterinarian_AI.jpg";
            senderNameForDisplay = messageUser.name || 'AI';
            senderNameForAlt = messageUser.name || 'AI';
        }
        // Final fallback check
        determinedProfileImageUrl = determinedProfileImageUrl || (isMyMessage ? '/images/default_user_avatar.png' : "images/veterinarian_AI.jpg");

        // Generate HTML
        const messageHtml = isMyMessage ?
            `
            <div class="d-flex justify-content-end mb-4 msg-container">
                <div class="msg_cotainer_send type_msg_text">
                    <span style="float: right;"><strong>${senderNameForDisplay}</strong></span><br>
                    ${message.message.replace(/\n/g, "<br>")}
                    <span class="msg_time_send ">${formatTime(message.created_at)}</span>
                </div>
                <div class="img_cont_msg">
                     <img src="${determinedProfileImageUrl}" class="rounded-circle user_img_msg" alt="${senderNameForAlt}">
                </div>
            </div>
            ` :
            `
            <div class="d-flex justify-content-start mb-4 msg-container">
                <div class="img_cont_msg">
                     <img src="${determinedProfileImageUrl}" class="rounded-circle user_img_msg" alt="${senderNameForAlt}">
                </div>
                <div class="msg_cotainer type_msg_text">
                    <strong>${senderNameForDisplay}:</strong><br>
                    ${message.message.replace(/\n/g, "<br>")}
                    <span class="msg_time">${formatTime(message.created_at)}</span>
                </div>
            </div>
            `;

        if (simpleBarInstance) {
            const contentEl = simpleBarInstance.getContentElement();
            $(contentEl).append(messageHtml);
             // SimpleBar needs to update its scrollbar visibility after content changes
             // This is often automatic but sometimes a manual update or timeout helps
             setTimeout(() => simpleBarInstance.getScrollElement().dispatchEvent(new Event('scroll')), 0);

        } else {
            // Fallback if SimpleBar didn't initialize, though less ideal
            $('#message-body').append(messageHtml);
            console.warn('SimpleBar not initialized, appending directly to #message-body');
        }

    }

    // --- Scrolling Function (Simplified Target) ---
    function scrollToBottom() {
        if (simpleBarInstance) {
            const scrollElement = simpleBarInstance.getScrollElement();
            // Scroll to the bottom. SimpleBar usually handles inertia.
            // A slight delay can sometimes help if content is still rendering.
            // Use a small timeout to allow rendering before scrolling
            setTimeout(() => {
                scrollElement.scrollTop = scrollElement.scrollHeight;
                 // Also trigger scroll event manually, sometimes helps SimpleBar update
                 scrollElement.dispatchEvent(new Event('scroll'));
            }, 50); // Small delay
        } else {
            // Fallback for non-SimpleBar scenario
            const mb = $('#message-body');
            if (mb.length) {
                mb.scrollTop(mb[0].scrollHeight);
            }
        }
    }

    // --- Chat Loading and Initialization ---
    function loadAIChat(chatId, initialMessages, chatPartnerInfo) {
        console.log('loadAIChat started for chat ID:', chatId);
        if (!chatId) {
             console.error("loadAIChat called without a chat ID.");
             // Clear UI to indicate no chat loaded
             if (simpleBarInstance) {
                  $(simpleBarInstance.getContentElement()).empty();
             } else {
                  messageBody.empty();
             }
             messageBody.append('<p class="text-muted text-center p-3">Error loading chat.</p>');
             messageCountEl.text('...');
             chatPartnerNameEl.text('Chat Error');
             chatPartnerImgEl.attr('src', 'asset(/images/veterinarian_AI.jpg)');
             sendMessageBtn.off('click');
             messageInput.prop('disabled', true).attr('placeholder', 'Error loading chat.');
             scrollToBottom();
             return;
        }

        // Unsubscribe from previous channel if different
        if (echoChannel && currentChatId && currentChatId !== chatId) {
             console.log('Leaving channel:', 'chat.' + currentChatId);
             window.Echo.leave('chat.' + currentChatId);
             echoChannel = null; // Explicitly nullify after leaving
        }

        currentChatId = chatId;
        $('#current-chat-id').val(chatId); // Update the hidden input

        // Update Header...
        if (chatPartnerInfo) {
            chatPartnerNameEl.text(chatPartnerInfo.name || 'AI Chat');
            chatPartnerImgEl.attr('src', chatPartnerInfo.image_url || "/images/veterinarian_AI.jpg");
        } else {
             // Default header if no partner info provided
             chatPartnerNameEl.text('AI Chat');
             chatPartnerImgEl.attr('src', "/images/veterinarian_AI.jpg");
        }

        // Clear and Render Initial Messages...
        if (simpleBarInstance) {
            $(simpleBarInstance.getContentElement()).empty(); // Clear SimpleBar's content container
        } else {
             messageBody.empty(); // Fallback clear
        }

        if (initialMessages && initialMessages.length > 0) {
            initialMessages.forEach(renderMessage);
            messageCountEl.text(initialMessages.length + ' Messages');
        } else {
            if (simpleBarInstance) {
                 $(simpleBarInstance.getContentElement()).append('<p class="text-muted text-center p-3">Start the conversation!</p>');
            } else {
                 messageBody.append('<p class="text-muted text-center p-3">Start the conversation!</p>');
            }
            messageCountEl.text('0 Messages');
        }

        scrollToBottom(); // Scroll ONCE after rendering all initial messages

        // Subscribe to Echo Channel...
        // Only subscribe if we don't have an active channel for this ID
        // console.log(!echoChannel , echoChannel.subscription.key !== 'private-chat.' + currentChatId);

        if (!echoChannel) {
            console.log('Attempting to subscribe to channel:', 'chat.' + currentChatId);
            echoChannel = window.Echo.private('chat.' + currentChatId)
                .listen('NewChatMessage', (e) => {
                    console.log('New message received via Pusher:', e);
                    if (e && e.message) {
                        renderMessage(e.message);
                        scrollToBottom(); // Scroll after rendering the NEW message
                    } else {
                         console.warn('Received empty or invalid message via Pusher:', e);
                    }
                })
                .error((error) => {
                     console.error('Pusher error on channel chat.' + currentChatId + ':', error);
                     // Handle potential connection issues or auth failures
                     // Maybe show a warning message in the chat?
                });
             console.log('Subscription process started for channel:', 'chat.' + currentChatId);
        } else {
             console.log('Already subscribed to channel:', 'chat.' + currentChatId);
        }


        // Enable Input...
        sendMessageBtn.off('click').on('click', sendMessage);
        messageInput.prop('disabled', false).attr('placeholder', 'Type your message...');
        // Only focus if the window is already visible
        if (chatWindow.is(':visible')) { messageInput.focus(); }
    }

    // --- Find/Create AI Chat via AJAX ---
    function findOrCreateAIChat() {
         console.log('Attempting to find or create AI chat...');

         // Update UI for loading state...
         chatPartnerNameEl.text('Connecting...');
         chatPartnerImgEl.attr('src', '/images/veterinarian_AI.jpg');
         messageCountEl.text('Loading...');

         // Clear previous messages and show loading indicator
         if (simpleBarInstance) {
              $(simpleBarInstance.getContentElement()).empty().append('<p class="text-muted text-center p-3">Loading AI chat...</p>');
         } else {
              messageBody.empty().append('<p class="text-muted text-center p-3">Loading AI chat...</p>');
         }

         scrollToBottom(); // Scroll for loading message

         // Disable input...
         sendMessageBtn.off('click');
         messageInput.prop('disabled', true).attr('placeholder', 'Loading...');

        $.ajax({
            url: `${window.APP_URL}/chat/ai/open`, // <-- This is the corrected line
            method: 'GET',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function(response) {
                console.log('AI Chat open response received:', response);
                if (response && response.chat_id && Array.isArray(response.messages) && response.chat_partner) {
                    loadAIChat(response.chat_id, response.messages, response.chat_partner);
                } else {
                     console.error('Invalid response structure from /chat/ai/open:', response);
                     // Handle error state in UI
                     if (simpleBarInstance) {
                         $(simpleBarInstance.getContentElement()).empty().append('<p class="text-muted text-center p-3">Failed to load chat.</p>');
                     } else {
                         messageBody.empty().append('<p class="text-muted text-center p-3">Failed to load chat.</p>');
                     }
                     messageCountEl.text('Error');
                     chatPartnerNameEl.text('Load Error');
                     sendMessageBtn.off('click');
                     messageInput.prop('disabled', true).attr('placeholder', 'Chat failed to load.');
                     scrollToBottom();
                }
            },
            error: function(xhr, status, error) {
                console.error('Error finding or creating AI chat:', xhr.status, xhr.responseText || error);
                 // Handle error state in UI
                 if (simpleBarInstance) {
                     $(simpleBarInstance.getContentElement()).empty().append('<p class="text-muted text-center p-3">Failed to load chat.</p>');
                 } else {
                     messageBody.empty().append('<p class="text-muted text-center p-3">Failed to load chat.</p>');
                 }
                 messageCountEl.text('Error');
                 chatPartnerNameEl.text('Load Error');
                 sendMessageBtn.off('click');
                 messageInput.prop('disabled', true).attr('placeholder', 'Chat failed to load.');
                 scrollToBottom();
            }
        });
    }

    // --- Send Message ---

    function sendMessage() {
        const messageText = messageInput.val().trim();
        if (messageText === '' || !currentChatId) {
             if (!currentChatId) {
                  console.warn("Cannot send message: currentChatId is not set.");
                  alert("Chat not initialized. Please try opening the chat window again.");
             }
             return;
        }

        const messageToSend = messageText;
        messageInput.val('');
        // Keep input enabled but clear it, show sending state via placeholder? Or disable briefly?
        // Disabling briefly is fine, but re-enable quickly in complete.
        messageInput.prop('disabled', true).attr('placeholder', 'Sending...');
        console.log('Input disabled for sending.');


        // --- tempMessage Creation for optimistic rendering ---
        // Use the correct user info for the temp message
        const tempMessage = {
            chat_id: currentChatId,
            message: messageToSend,
            // Use client-side time for optimistic display, actual timestamp comes from backend
            created_at: new Date().toISOString(),
            user: {
                id: currentUserId,
                type: 'App\\Models\\User',
                name: 'You', // Display name 'You' for current user
                profile_picture: currentUserImgUrl,
                image_url: currentUserImgUrl
            }
        };

        console.log("Attempting to render optimistic message with tempMessage:", JSON.stringify(tempMessage));

        renderMessage(tempMessage); // Optimistically render the sent message
        scrollToBottom(); // Scroll to show the new optimistic message

        console.log('Initiating AJAX send...');
        $.ajax({
            url: `${window.APP_URL}/chat/message/send`,
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: { chat_id: currentChatId, message: messageToSend },
            success: function(response) {
                console.log('Message sent success (backend processed):', response);
                // Do NOT render the message again here. The broadcast listener handles rendering
                // the *final* version of the message (which might include server-generated ID/timestamp).
                // The optimistic message will be replaced/matched by the broadcasted one.
            },
            error: function(xhr, status, error) {
                console.error('Error sending message:', xhr.status, xhr.responseText || error);
                // Basic error handling: re-populate input, show alert
                messageInput.val(messageToSend); // Restore message
                alert('Error sending message. Please try again.');
                // Consider removing the optimistic message if sending failed permanently?
                // This requires more complex UI state management. For now, just restoring input.
            },
            complete: function(jqXHR, textStatus) {
                console.log('AJAX complete callback triggered. Status:', textStatus);
                // Re-enable input regardless of success/error
                messageInput.prop('disabled', false);
                messageInput.attr('placeholder', 'Type your message...');
                messageInput.focus(); // Re-focus
                console.log('Input re-enabled and focused.');
            }
        });
    }

    // --- Clear Chat Function ---
    function clearChat() {
        if (!currentChatId) {
            alert('No active chat to clear.');
            return;
        }

        if (!confirm('Are you sure you want to clear all messages in this chat? This cannot be undone.')) {
            return;
        }

        console.log('Attempting to clear messages for chat ID:', currentChatId);
        clearChatBtn.prop('disabled', true).addClass('btn-secondary').removeClass('btn-danger'); // Disable button and change style

        $.ajax({
            url: `${window.APP_URL}/chat/${currentChatId}/clear`,
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                console.log('Clear messages response:', response);
                if (response.status === 'Messages cleared!') {
                    // Clear the SimpleBar content container
                    if (simpleBarInstance) {
                        $(simpleBarInstance.getContentElement()).empty();
                         $(simpleBarInstance.getContentElement()).append('<p class="text-muted text-center p-3">Chat cleared. Start a new conversation!</p>');
                    } else {
                        // Fallback clear
                        messageBody.empty();
                        messageBody.append('<p class="text-muted text-center p-3">Chat cleared. Start a new conversation!</p>');
                    }

                    messageCountEl.text('0 Messages');
                    findOrCreateAIChat();
                    // scrollToBottom(); // Ensure scrollbar is updated

                    console.log('Chat cleared successfully. Frontend updated.');
                    alert('Chat messages cleared successfully!');

                    // IMPORTANT FIX: Do NOT call findOrCreateAIChat here.
                    // Wait for the user to send the first message to trigger the next conversation.
                    // The input is already enabled by the AJAX complete handler of sendMessage,
                    // or it will be enabled when the chat window is re-opened if closed.
                    // The chat ID remains assigned (`currentChatId`) unless the window is closed.

                } else {
                    alert('Failed to clear messages: ' + (response.error || 'Unknown error'));
                }
            },
            error: function(xhr, status, error) {
                console.error('Error clearing messages:', xhr.responseText || error);
                alert('Error clearing messages. Please try again.');
            },
            complete: function() {
                // Re-enable clear button regardless of success/error
                clearChatBtn.prop('disabled', false).addClass('btn-danger').removeClass('btn-secondary');
            }
        });
    }


    // --- Event Handlers ---
    chatToggleBtn.on('click', function() {
        chatWindow.slideToggle(300, function() {
            if (chatWindow.is(':visible')) {
                // When chat opens, ALWAYS call findOrCreateAIChat.
                // This handles initial load, or re-loading state if chat ID persists.
                // The backend should handle not sending a greeting if the chat isn't truly new/empty.
                // Or if the chat becomes empty after clear, the *next* message sent by the user
                // should trigger the AI's first response/greeting.
                findOrCreateAIChat();
            } else { // Cleanup on close
                console.log('Chat window closing. Cleaning up.');
                // Leave the Echo channel
                if (echoChannel && currentChatId) {
                    console.log('Leaving channel on close:', 'chat.' + currentChatId);
                    window.Echo.leave('chat.' + currentChatId);
                    echoChannel = null;
                }
                // Hide the action menu
                actionMenu.hide();
                // Clear chat ID state only on close
                currentChatId = null;
                $('#current-chat-id').val(''); // Clear the hidden input

                // Clear the message body UI using SimpleBar's content element
                if (simpleBarInstance) {
                     $(simpleBarInstance.getContentElement()).empty();
                     $(simpleBarInstance.getContentElement()).append('<p class="text-muted text-center p-3">Chat closed.</p>');
                } else {
                     messageBody.empty();
                     messageBody.append('<p class="text-muted text-center p-3">Chat closed.</p>');
                }

                messageCountEl.text('...');
                chatPartnerNameEl.text('Chat Closed');
                chatPartnerImgEl.attr('src', "/images/veterinarian_AI.jpg");
                sendMessageBtn.off('click');
                messageInput.prop('disabled', true).attr('placeholder', 'Chat closed.');
                scrollToBottom(); // Ensure scrollbar updates correctly for closed state
            }
        });
    });

    messageInput.on('keypress', function(e) {
         if (e.which === 13 && !e.shiftKey) { e.preventDefault(); if (!messageInput.prop('disabled')) { sendMessage(); } }
     });

    // Assuming you still have action menu button and menu
    actionMenuBtn.on('click', function() {
        console.log('Action menu button clicked.');
        actionMenu.toggle(); // Toggle the generic action menu if it exists
    });

    $(document).on('click', function(e){
        // Hide action menu on outside click
        const $actionMenuBtn = $('#action_menu_btn');
        const $actionMenu = $('.action_menu');
        if (!$actionMenuBtn.is(e.target) && $actionMenuBtn.has(e.target).length === 0 &&
            !$actionMenu.is(e.target) && $actionMenu.has(e.target).length === 0) {
            if ($actionMenu.is(':visible')) {
                 console.log('Hiding action menu (click outside).');
                 $actionMenu.hide();
            }
        }
    });

    // EVENT LISTENER FOR CLEAR CHAT BUTTON
    clearChatBtn.on('click', function(e) {
        e.preventDefault();
        console.log('Clear chat button clicked.');
        clearChat();
    });

    // Initial load logic - find or create AI chat when the page loads *if* the chat window is initially visible
    // Or if you always want an AI chat loaded even when hidden, call findOrCreateAIChat here directly.
    // Based on your toggle logic, it seems you only want to load when shown.
    // So no initial call needed here, the toggle handler takes care of it.


}); // End $(document).ready()
