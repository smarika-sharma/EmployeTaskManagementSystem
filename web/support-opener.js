// Support Modal Opener Functions

// Function to open support modal in a popup window
function openSupportModal() {
    const popup = window.open(
        'support-modal.html',
        'SupportModal',
        'width=1000,height=800,scrollbars=yes,resizable=yes,menubar=no,toolbar=no,location=no,status=no,centerscreen=yes'
    );
    
    // Focus the popup and handle if popup was blocked
    if (popup) {
        popup.focus();
        // Check if popup was blocked
        if (popup.closed || typeof popup.closed === 'undefined') {
            alert('Popup was blocked. Please allow popups for this site and try again.');
        }
    } else {
        // Fallback if popup was blocked
        alert('Popup was blocked. Please allow popups for this site and try again.');
    }
}

// Function to open support modal in the same window (alternative approach)
function openSupportModalSameWindow() {
    window.location.href = 'support-modal.html';
}

// Function to open support modal as an iframe overlay (another alternative)
function openSupportModalIframe() {
    // Create overlay
    const overlay = document.createElement('div');
    overlay.id = 'supportOverlay';
    overlay.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1000;
        display: flex;
        justify-content: center;
        align-items: center;
    `;
    
    // Create modal container
    const modal = document.createElement('div');
    modal.style.cssText = `
        background: white;
        border-radius: 16px;
        width: 90%;
        max-width: 1000px;
        height: 90%;
        max-height: 800px;
        overflow: hidden;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        position: relative;
    `;
    
    // Create close button
    const closeBtn = document.createElement('button');
    closeBtn.innerHTML = '✕';
    closeBtn.style.cssText = `
        position: absolute;
        top: 1rem;
        right: 1rem;
        background: #f3f4f6;
        border: none;
        font-size: 1.5rem;
        color: #6b7280;
        cursor: pointer;
        z-index: 1001;
        width: 40px;
        height: 40px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
    `;
    
    // Create iframe
    const iframe = document.createElement('iframe');
    iframe.src = 'support-modal.html';
    iframe.style.cssText = `
        width: 100%;
        height: 100%;
        border: none;
        border-radius: 16px;
    `;
    
    // Add elements to DOM
    modal.appendChild(closeBtn);
    modal.appendChild(iframe);
    overlay.appendChild(modal);
    document.body.appendChild(overlay);
    
    // Close functionality
    function closeModal() {
        if (document.body.contains(overlay)) {
            document.body.removeChild(overlay);
        }
    }
    
    // Add event listeners
    closeBtn.addEventListener('click', closeModal);
    
    overlay.addEventListener('click', function(e) {
        if (e.target === overlay) {
            closeModal();
        }
    });
    
    // Close with Escape key
    const escapeHandler = function(e) {
        if (e.key === 'Escape') {
            closeModal();
            document.removeEventListener('keydown', escapeHandler);
        }
    };
    document.addEventListener('keydown', escapeHandler);
    
    // Add hover effect to close button
    closeBtn.addEventListener('mouseenter', function() {
        this.style.background = '#e5e7eb';
        this.style.color = '#374151';
    });
    
    closeBtn.addEventListener('mouseleave', function() {
        this.style.background = '#f3f4f6';
        this.style.color = '#6b7280';
    });
}

// Usage examples:
// 1. For popup window (recommended):
// <button onclick="openSupportModal()">Support</button>

// 2. For same window navigation:
// <button onclick="openSupportModalSameWindow()">Support</button>

// 3. For iframe overlay:
// <button onclick="openSupportModalIframe()">Support</button> 