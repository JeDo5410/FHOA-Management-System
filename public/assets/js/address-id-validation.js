// address-id-validation.js
document.addEventListener('DOMContentLoaded', function() {
    // Get the address_id input field from index.blade.php
    const addressIdField = document.getElementById('address_id');
    
    if (addressIdField) {
        // Set the placeholder
        addressIdField.setAttribute('placeholder', 'Enter PhaseLotBlock');
        addressIdField.setAttribute('autocomplete', 'off');
        
        // Remove any attributes that might restrict input
        addressIdField.removeAttribute('pattern');
        addressIdField.removeAttribute('inputmode');
        
        // Add validation for first character to be only 1 or 2
        addressIdField.addEventListener('keypress', (e) => {
            // Get current input value and cursor position
            const value = e.target.value;
            const position = e.target.selectionStart;
            
            // If typing at the first position, only allow 1 or 2
            if (position === 0) {
                if (!(e.key === '1' || e.key === '2')) {
                    e.preventDefault();
                    return false;
                }
            }
            
            // Prevent input if already at max length
            if (value.length >= 5 && position >= 5 && !e.ctrlKey && !e.metaKey) {
                e.preventDefault();
                return false;
            }
        });
        
        // Additional validation on input event
        addressIdField.addEventListener('input', (e) => {
            let value = e.target.value;
            
            // Ensure first character is only 1 or 2
            if (value.length > 0 && value[0] !== '1' && value[0] !== '2') {
                // Remove invalid first character
                e.target.value = value.substring(1);
                value = e.target.value;
            }
            
            // Limit to 5 characters
            if (value.length > 5) {
                e.target.value = value.slice(0, 5);
            }
        });
    }
    
    // Add validation for any address ID inputs added dynamically later
    const observeDOM = (function() {
        const MutationObserver = window.MutationObserver || window.WebKitMutationObserver;
        
        return function(obj, callback) {
            if (!obj || obj.nodeType !== 1) return;
            
            if (MutationObserver) {
                // Define a new observer
                const mutationObserver = new MutationObserver(callback);
                
                // Have the observer observe the element for changes in children
                mutationObserver.observe(obj, { childList: true, subtree: true });
                return mutationObserver;
            } else if (window.addEventListener) {
                obj.addEventListener('DOMNodeInserted', callback, false);
                obj.addEventListener('DOMNodeRemoved', callback, false);
            }
        };
    })();
    
    // Watch for dynamically added address inputs
    observeDOM(document.body, function() {
        const newAddressInputs = document.querySelectorAll('input[name="address_id"]:not([data-validated])');
        newAddressInputs.forEach(input => {
            input.setAttribute('placeholder', 'Enter PhaseLotBlock');
            input.setAttribute('autocomplete', 'off');
            input.setAttribute('data-validated', 'true');
            
            // Add same validation as above
            // (Duplicate the validation logic here for any new elements)
        });
    });
});