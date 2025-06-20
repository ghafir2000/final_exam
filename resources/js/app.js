// In resources/js/app.js

import './bootstrap'; // This should be the VERY FIRST import

// Now import your other custom JS files that might depend on jQuery or Echo
import './notification_navbar'; // This will now have access to window.Echo and window.$
import './AI-chat.js';        // This will now have access to window.$