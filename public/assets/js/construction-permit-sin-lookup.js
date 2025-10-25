// construction-permit-sin-lookup.js
// SIN lookup functionality for Construction Permit module

document.addEventListener('DOMContentLoaded', function() {
    setupConstructionPermitSinLookup();
});

function setupConstructionPermitSinLookup() {
    const permitSinField = document.getElementById('permitSin');
    
    if (permitSinField) {
        // Add blur event listener
        permitSinField.addEventListener('blur', function() {
            handleSinLookup(this.value.trim());
        });
        
        // Add Enter keypress event listener
        permitSinField.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                handleSinLookup(this.value.trim());
            }
        });
    }
}

function handleSinLookup(sinValue) {
    // Skip lookup if empty or zero
    if (!sinValue || sinValue === '0' || sinValue === '') {
        return;
    }

    // Convert to integer to remove leading zeros
    const sinNumber = parseInt(sinValue, 10);

    // Skip if not a valid number (but allow negative numbers)
    if (isNaN(sinNumber) || sinNumber === 0) {
        return;
    }
    
    // Show loading message
    triggerToast('info', `Checking construction permit SIN #${sinNumber}...`);
    
    // Make API call to check SIN
    fetch(`/construction-permit/check-sin/${sinNumber}`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.exists) {
            // SIN exists and is a construction fee - populate the fields
            populateConstructionPermitFields(data.transaction);
            triggerToast('success', 'Construction permit SIN found and loaded successfully');
        } else {
            // SIN doesn't exist or is not a construction fee type
            clearConstructionPermitFields();
            triggerToast('error', data.message);
        }
    })
    .catch(error => {
        console.error('Error checking construction permit SIN:', error);
        clearConstructionPermitFields();
        triggerToast('error', 'Error checking SIN: ' + error.message);
    });
}

function populateConstructionPermitFields(transaction) {
    // Populate Amount Paid field
    const amountPaidField = document.getElementById('amountPaid');
    if (amountPaidField && transaction.ar_amount) {
        const amount = Math.abs(parseFloat(transaction.ar_amount)); // Use absolute value
        amountPaidField.value = amount.toFixed(2);
        amountPaidField.disabled = false; // Ensure the field is enabled for form submission
        amountPaidField.readOnly = true;  // Make the field non-editable
    }
    
    // Populate Paid Date field
    const paidDateField = document.getElementById('paidDate');
    if (paidDateField && transaction.ar_date) {
        // Format the date for input[type="date"] (YYYY-MM-DD)
        const date = new Date(transaction.ar_date);
        const formattedDate = date.toISOString().split('T')[0];
        paidDateField.value = formattedDate;
        paidDateField.disabled = false; // Ensure the field is enabled for form submission
        paidDateField.readOnly = true;  // Make the field non-editable
    }
    
    console.log('Construction permit fields populated:', {
        sin: transaction.or_number,
        amount: transaction.ar_amount,
        date: transaction.ar_date
    });
}

function clearConstructionPermitFields() {
    // Clear Amount Paid field
    const amountPaidField = document.getElementById('amountPaid');
    if (amountPaidField) {
        amountPaidField.value = '';
        amountPaidField.disabled = true;  // Keep disabled when empty
        amountPaidField.readOnly = false; // Remove read-only state
    }
    
    // Clear Paid Date field
    const paidDateField = document.getElementById('paidDate');
    if (paidDateField) {
        paidDateField.value = '';
        paidDateField.disabled = true;  // Keep disabled when empty
        paidDateField.readOnly = false; // Remove read-only state
    }
    
    console.log('Construction permit fields cleared');
}

// Utility function to show toast notifications
// This relies on the showToast function being available globally
// function showToast(type, message) {
//     if (typeof window.showToast === 'function') {
//         window.showToast(type, message);
//     } else {
//         // Fallback to console if toast function not available
//         console.log(`${type.toUpperCase()}: ${message}`);
//     }
// }
function triggerToast(type, message) {
    // The global showToast function is defined in the main Blade template.
    if (typeof window.showToast === 'function') {
        window.showToast(type, message);
    } else {
        // Fallback to console if toast function not available
        console.log(`Toast fallback (${type.toUpperCase()}): ${message}`);
    }
}
