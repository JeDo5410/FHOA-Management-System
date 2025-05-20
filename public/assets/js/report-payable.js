document.addEventListener('DOMContentLoaded', function() {
    // Set default date range to current month
    const now = new Date();
    const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
    const lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0);
    
    // Format dates as YYYY-MM-DD
    const formatDate = (date) => {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    };
    
    // Set default values for date inputs
    const startDateInput = document.getElementById('payableStartDate');
    const endDateInput = document.getElementById('payableEndDate');
    
    startDateInput.value = formatDate(firstDay);
    endDateInput.value = formatDate(lastDay);
    
    // Setup scroll sync for payable table
    function setupPayableScrollSync() {
        const payableTableContainer = document.getElementById('payableTableContainer');
        const payableStickyScrollbar = document.getElementById('payableStickyScrollbar');
        
        if (payableTableContainer && payableStickyScrollbar) {
            // Add scroll listener to table container
            payableTableContainer.onscroll = function() {
                payableStickyScrollbar.scrollLeft = payableTableContainer.scrollLeft;
            };
            
            // Add scroll listener to sticky scrollbar
            payableStickyScrollbar.onscroll = function() {
                payableTableContainer.scrollLeft = payableStickyScrollbar.scrollLeft;
            };
        }
    }
    
    // Update scrollbar width for payable table
    function updatePayableScrollbarWidth() {
        const payableTable = document.getElementById('payableDataTable');
        const payableScrollbarContent = document.getElementById('payableScrollbarContent');
        
        if (payableTable && payableScrollbarContent) {
            // Use scrollWidth to get the full scrollable width
            payableScrollbarContent.style.width = (payableTable.scrollWidth + 10) + 'px';
        }
    }
    
    // Load account payable data with date range filter
    function loadPayableData() {
        // Get date range values
        const startDate = document.getElementById('payableStartDate').value;
        const endDate = document.getElementById('payableEndDate').value;
        
        // Show loading indicator
        const tbody = document.querySelector('#payableDataTable tbody');
        tbody.innerHTML = '<tr><td colspan="14" class="text-center">Loading data...</td></tr>';
        
        // Fetch data from server
        fetch(`/reports/get-payable-data?start_date=${startDate}&end_date=${endDate}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                // Clear loading indicator
                tbody.innerHTML = '';
                
                if (data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="14" class="text-center">No data found</td></tr>';
                    showToast('info', 'No account payable records found for the selected date range');
                    return;
                }
                
                // Show count notification
                showToast('success', `Found ${data.length} account payable records`);
                
                // Format currency
                const formatter = new Intl.NumberFormat('en-PH', {
                    style: 'currency',
                    currency: 'PHP',
                    minimumFractionDigits: 2
                });
                
                // Add rows
                data.forEach(payable => {
                    const row = document.createElement('tr');
                    
                    // Format date
                    const apDate = payable.ap_date ? new Date(payable.ap_date).toLocaleDateString() : 'N/A';
                    
                    // Format timestamp
                    const timestamp = payable.timestamp ? new Date(payable.timestamp).toLocaleString() : 'N/A';
                    
                    // Format currency fields
                    const total = payable.ap_total ? formatter.format(payable.ap_total) : '₱0.00';
                    const amount = payable.ap_amount ? formatter.format(payable.ap_amount) : '₱0.00';
                    
                    row.innerHTML = `
                        <td>${payable.ap_transno || ''}</td>
                        <td>${payable.ap_voucherno || ''}</td>
                        <td>${apDate}</td>
                        <td>${payable.ap_payee || ''}</td>
                        <td>${payable.ap_paytype || ''}</td>
                        <td>${payable.paytype_reference || ''}</td>
                        <td>${total}</td>
                        <td>${payable.ap_particular || ''}</td>
                        <td>${amount}</td>
                        <td>${payable.acct_type || ''}</td>
                        <td>${payable.acct_name || ''}</td>
                        <td>${payable.remarks || ''}</td>
                        <td>${payable.user_fullname || ''}</td>
                        <td>${timestamp}</td>
                    `;
                    
                    tbody.appendChild(row);
                });
                
                // Update scrollbar width
                updatePayableScrollbarWidth();
            })
            .catch(error => {
                console.error('Error loading payable data:', error);
                showToast('error', 'Failed to load account payable data. Please try again.');
                tbody.innerHTML = '<tr><td colspan="14" class="text-center text-danger">Error loading data</td></tr>';
            });
    }
    
    // Apply filter button event listener
    document.getElementById('applyPayableFilterBtn').addEventListener('click', function() {
        loadPayableData();
    });
    
    // Download CSV button event listener
    document.getElementById('downloadPayableBtn').addEventListener('click', function() {
        const startDate = document.getElementById('payableStartDate').value;
        const endDate = document.getElementById('payableEndDate').value;
        
        // Create download URL with filters
        const downloadUrl = `/reports/download/payable-data?start_date=${startDate}&end_date=${endDate}`;
        
        // Redirect to download URL
        window.location.href = downloadUrl;
    });
    
    // Tab change event to update scrollbar
    document.getElementById('payable-tab').addEventListener('shown.bs.tab', function (e) {
        setTimeout(function() {
            updatePayableScrollbarWidth();
            setupPayableScrollSync();
            loadPayableData(); // Load data when tab is shown
        }, 100);
    });
    
    // Setup scroll sync
    setupPayableScrollSync();
    
    // Check if payable tab is active on page load
    if (document.getElementById('payable-tab').classList.contains('active')) {
        loadPayableData();
    }
});