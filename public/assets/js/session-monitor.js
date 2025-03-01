class SessionMonitor {
    constructor(options = {}) {
        this.options = {
            checkInterval: options.checkInterval || 60000, // 1 minute default
            sessionTimeout: options.sessionTimeout || 7200000, // 2 hours default (match your Laravel setting)
            checkUrl: options.checkUrl || '/check-session-status',
            redirectUrl: options.redirectUrl || '/login?reason=timeout',
        };
        
        this.checkIntervalId = null;
        this.lastActivity = Date.now();
        
        // Bind event listeners to reset the timer on user activity
        const activityEvents = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart'];
        activityEvents.forEach(event => {
            document.addEventListener(event, () => this.resetActivityTimer());
        });
    }
    
    start() {
        // Reset activity timer
        this.resetActivityTimer();
        
        // Start the check interval
        this.checkIntervalId = setInterval(() => this.checkSession(), this.options.checkInterval);
        console.log('Session monitoring started');
    }
    
    stop() {
        if (this.checkIntervalId) {
            clearInterval(this.checkIntervalId);
            this.checkIntervalId = null;
        }
        console.log('Session monitoring stopped');
    }
    
    resetActivityTimer() {
        this.lastActivity = Date.now();
    }
    
    checkSession() {
        // Calculate time since last activity
        const inactiveTime = Date.now() - this.lastActivity;
        
        // If inactive time exceeds session timeout, check with server
        if (inactiveTime >= this.options.sessionTimeout) {
            this.verifySessionWithServer();
            return;
        }
        
        // Periodically verify with server regardless of activity
        this.verifySessionWithServer();
    }
    
    verifySessionWithServer() {
        fetch(this.options.checkUrl, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        })
        .then(response => {
            if (!response.ok) {
                this.redirectToLogin();
            }
        })
        .catch(() => {
            // On error, we might want to verify again or redirect
            console.error('Failed to verify session status');
        });
    }
    
    redirectToLogin() {
        window.location.href = this.options.redirectUrl;
    }
}

// Initialize and start session monitoring when document is ready
document.addEventListener('DOMContentLoaded', () => {
    // Get session timeout from server (in milliseconds)
    const sessionTimeoutMins = parseInt(document.body.dataset.sessionTimeout || '120'); // default 120 minutes
    
    const sessionMonitor = new SessionMonitor({
        checkInterval: 60000, // Check every minute
        sessionTimeout: sessionTimeoutMins * 60 * 1000, // Convert minutes to milliseconds
        redirectUrl: '/login?reason=timeout'
    });
    
    sessionMonitor.start();
});