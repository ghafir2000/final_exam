document.addEventListener('DOMContentLoaded', function () {
    // --- START: Notification Fetching Logic (remains the same) ---
    const notificationCountElement = document.getElementById('notification-count');
    const notificationDropdownMenu = document.getElementById('notification-dropdown-menu');
    const navbarDropdownNotifications = document.getElementById('navbarDropdownNotifications');

    function getTimeAgo(date) {
        const seconds = Math.floor((new Date() - date) / 1000);
        if (seconds < 5) return "just now";
        let interval = Math.floor(seconds / 31536000);
        if (interval >= 1) return interval + (interval === 1 ? " year ago" : " years ago");
        interval = Math.floor(seconds / 2592000);
        if (interval >= 1) return interval + (interval === 1 ? " month ago" : " months ago");
        interval = Math.floor(seconds / 86400);
        if (interval >= 1) return interval + (interval === 1 ? " day ago" : " days ago");
        interval = Math.floor(seconds / 3600);
        if (interval >= 1) return interval + (interval === 1 ? " hour ago" : " hours ago");
        interval = Math.floor(seconds / 60);
        if (interval >= 1) return interval + (interval === 1 ? " minute ago" : " minutes ago");
        return Math.floor(seconds) + " seconds ago";
    }

    async function fetchNotificationCount() {
        if (!notificationCountElement || !navbarDropdownNotifications) {
            return;
        }
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        try {
            const response = await fetch(`${window.APP_URL}/api/notifications/unread-count-and-latest`, {                 method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    ...(csrfToken && {'X-CSRF-TOKEN': csrfToken})
                },
                credentials: 'include'
            });

            if (!response.ok) {
                console.error('Failed to fetch notifications - HTTP status:', response.status, response.statusText);
                try { const errorText = await response.text(); console.error("Response body (if not ok):", errorText.substring(0, 500)); } catch (e) { console.error("Could not read error response body on !response.ok.");}
                return;
            }

            const contentType = response.headers.get("content-type");
            if (!contentType || contentType.indexOf("application/json") === -1) {
                console.error("API did not return JSON. Content-Type received:", contentType);
                const responseText = await response.text(); console.error("Response body (not JSON):", responseText.substring(0, 500));
                return;
            }

            const data = await response.json();

            if (data.count > 0) {
                notificationCountElement.textContent = data.count;
                notificationCountElement.style.display = '';
            } else {
                notificationCountElement.textContent = '';
                notificationCountElement.style.display = 'none';
            }

            if (notificationDropdownMenu) {
                let dropdownHtml = '';
                if (data.notifications && data.notifications.length > 0) {
                    data.notifications.forEach(notification => {
                        // The 'notification.data' comes from your database record,
                        // which should align with what toDatabase() stores.
                        const message = notification.data.message ? notification.data.message.substring(0, 50) : 'New Notification';
                        // The URL in the DB record is notification.data.url
                        const baseUrl = notification.data.url || '#';
                        // Construct URL to mark as read. The `notification.id` is the DB notification ID.
                        const markAsReadUrl = `${baseUrl}${baseUrl.includes('?') ? '&' : '?'}markAsRead=${notification.id}`;
                        const timeAgo = getTimeAgo(new Date(notification.created_at));

                        dropdownHtml += `
                            <a class="dropdown-item" href="${markAsReadUrl}">
                                <div>${message}</div>
                                <small class="text-notification">${timeAgo}</small>
                            </a>`;
                    });
                    dropdownHtml += '<div class="dropdown-divider"></div>';
                    dropdownHtml += `<a class="dropdown-item text-center small" href="/notifications">${window.i18n.allNotifications}</a>`;
                  } else {
                    // Corrected line:
                    // Blade will process {{ __("...") }} and replace it with the translated string
                dropdownHtml = `<span class="dropdown-item text-notification text-center small">${window.i18n.noNewNotifications}</span>`;                  }
                notificationDropdownMenu.innerHTML = dropdownHtml;
            }
        } catch (error) {
            console.error('Error in fetchNotificationCount (e.g., network issue, or .json() failed):', error);
        }
    }

    if (navbarDropdownNotifications) {
        fetchNotificationCount(); // Initial load
        // setInterval(fetchNotificationCount, 30000); // Keep polling commented out for testing real-time
    }
    // --- END: Notification Fetching Logic ---


    // --- START: Real-time Event Listening Logic ---
    if (window.Echo) {
        const userIdMeta = document.querySelector("meta[name='user-id']");

        if (userIdMeta) {
            const userId = userIdMeta.getAttribute("content");

            // Listen on the private channel for the specific event class name
            // The event name is 'NewNotification' (class name without namespace),
            // prefixed with a dot. Or, if you use broadcastAs() in your Event, use that name.
            window.Echo.private(`App.Models.User.${userId}`)
                .listen('.NewNotification', (eventData) => { // Using .listen for a specific event
                    console.log('Real-time event "NewNotification" received via Pusher:', eventData);
                    // eventData will be the payload from NewNotification::broadcastWith()
                    // e.g., { product_id: ..., product_name: ..., message: ..., url: ... }

                    // Since a new notification event occurred, re-fetch the list
                    // from the database to get the updated count and items.
                    // The database was updated by ProductAddedToCartNotification.
                    fetchNotificationCount();

                    // Optional: Show a toast or some other immediate feedback
                    // alert("New activity: " + eventData.message);
                });

            console.log(`Subscribed to App.Models.User.${userId} for NewNotification events`);

        } else {
            console.warn('User ID meta tag not found. Cannot listen for private notifications.');
        }
    } else {
        console.error('Laravel Echo not initialized. Real-time notifications will not work.');
    }
    // --- END: Real-time Event Listening Logic ---


    // --- START: Navbar Scroll Effects (remains the same) ---
    const navbar = document.querySelector('.main-navbar');
    let lastScrollTop = 0;
    const scrollThreshold = 50;
    const hideShowThreshold = 5;

    if (navbar) {
        function handleScroll() {
            let currentScrollPos = window.pageYOffset || document.documentElement.scrollTop;
            if (currentScrollPos > scrollThreshold) { navbar.classList.add('scrolled'); } else { navbar.classList.remove('scrolled'); }
            if (currentScrollPos > lastScrollTop && currentScrollPos > (navbar.offsetHeight || 70)) {
                if (Math.abs(currentScrollPos - lastScrollTop) > hideShowThreshold) { navbar.classList.add('navbar-hidden'); }
            } else {
                if (Math.abs(currentScrollPos - lastScrollTop) > hideShowThreshold || currentScrollPos < (navbar.offsetHeight || 70)) { navbar.classList.remove('navbar-hidden'); }
            }
            lastScrollTop = currentScrollPos <= 0 ? 0 : currentScrollPos;
        }
        handleScroll();
        window.addEventListener('scroll', handleScroll, false);
    }
    // --- END: Navbar Scroll Effects ---
});