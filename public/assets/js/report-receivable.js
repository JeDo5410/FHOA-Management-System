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
    const startDateInput = document.getElementById('receivableStartDate');
    const endDateInput = document.getElementById('receivableEndDate');
    
    startDateInput.value = formatDate(firstDay);
    endDateInput.value = formatDate(lastDay);
    
    // Setup scroll sync for receivable table
    function setupReceivableScrollSync() {
        const receivableTableContainer = document.getElementById('receivableTableContainer');
        const receivableStickyScrollbar = document.getElementById('receivableStickyScrollbar');
        
        if (receivableTableContainer && receivableStickyScrollbar) {
            // Add scroll listener to table container
            receivableTableContainer.onscroll = function() {
                receivableStickyScrollbar.scrollLeft = receivableTableContainer.scrollLeft;
            };
            
            // Add scroll listener to sticky scrollbar
            receivableStickyScrollbar.onscroll = function() {
                receivableTableContainer.scrollLeft = receivableStickyScrollbar.scrollLeft;
            };
        }
    }
    
    // Update scrollbar width for receivable table
    function updateReceivableScrollbarWidth() {
        const receivableTable = document.getElementById('receivableDataTable');
        const receivableScrollbarContent = document.getElementById('receivableScrollbarContent');
        
        if (receivableTable && receivableScrollbarContent) {
            // Use scrollWidth to get the full scrollable width
            receivableScrollbarContent.style.width = (receivableTable.scrollWidth + 10) + 'px';
        }
    }
    
    // Load account receivable data with date range filter
    function loadReceivableData() {
        // Get date range values
        const startDate = document.getElementById('receivableStartDate').value;
        const endDate = document.getElementById('receivableEndDate').value;
        
        // Show loading indicator
        const tbody = document.querySelector('#receivableDataTable tbody');
        tbody.innerHTML = '<tr><td colspan="14" class="text-center">Loading data...</td></tr>';
        
        // Fetch data from server
        fetch(`/reports/get-receivable-data?start_date=${startDate}&end_date=${endDate}`)
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
                    showToast('info', 'No account receivable records found for the selected date range');
                    return;
                }
                
                // Show count notification
                showToast('success', `Found ${data.length} account receivable records`);
                
                // Format currency
                const formatter = new Intl.NumberFormat('en-PH', {
                    style: 'currency',
                    currency: 'PHP',
                    minimumFractionDigits: 2
                });
                
                // Add rows
                    data.forEach(receivable => {
                    const row = document.createElement('tr');

                    // Format date
                    const arDate = receivable.ar_date ? new Date(receivable.ar_date).toLocaleDateString() : 'N/A';

                    // Format timestamp
                    const timestamp = receivable.timestamp ? new Date(receivable.timestamp).toLocaleString() : 'N/A';

                    // Format currency fields
                    const amount = receivable.ar_amount ? formatter.format(receivable.ar_amount) : '₱0.00';
                    const arrearBal = receivable.arrear_bal ? formatter.format(receivable.arrear_bal) : '₱0.00';

                    row.innerHTML = `
                        <td>${receivable.ar_transno || ''}</td>
                        <td>${receivable.or_number || ''}</td>
                        <td>${arDate}</td>
                        <td>${amount}</td>
                        <td>${arrearBal}</td>
                        <td>${receivable.acct_description || ''}</td>
                        <td>${receivable.payor_name || ''}</td>
                        <td>${receivable.payor_address || ''}</td>
                        <td>${receivable.payment_type || ''}</td>
                        <td>${receivable.payment_Ref || ''}</td>
                        <td>${receivable.receive_by || ''}</td>
                        <td>${receivable.ar_remarks || ''}</td>
                        <td>${receivable.user_fullname || ''}</td>
                        <td>${timestamp}</td>
                    `;

                    tbody.appendChild(row);
                    });
                
                // Update scrollbar width
                updateReceivableScrollbarWidth();
            })
            .catch(error => {
                console.error('Error loading receivable data:', error);
                showToast('error', 'Failed to load account receivable data. Please try again.');
                tbody.innerHTML = '<tr><td colspan="14" class="text-center text-danger">Error loading data</td></tr>';
            });
    }
    
    // Apply filter button event listener
    document.getElementById('applyReceivableFilterBtn').addEventListener('click', function() {
        loadReceivableData();
    });
    
    // Download CSV button event listener
    document.getElementById('downloadReceivableBtn').addEventListener('click', function() {
        const startDate = document.getElementById('receivableStartDate').value;
        const endDate = document.getElementById('receivableEndDate').value;
        
        // Create download URL with filters
        const downloadUrl = `/reports/download/receivable-data?start_date=${startDate}&end_date=${endDate}`;
        
        // Redirect to download URL
        window.location.href = downloadUrl;
    });
    
    // Tab change event to update scrollbar
    document.getElementById('receivable-tab').addEventListener('shown.bs.tab', function (e) {
        setTimeout(function() {
            updateReceivableScrollbarWidth();
            setupReceivableScrollSync();
            loadReceivableData(); // Load data when tab is shown
        }, 100);
    });
    
    // Setup scroll sync
    setupReceivableScrollSync();
    
    // Check if receivable tab is active on page load
    if (document.getElementById('receivable-tab').classList.contains('active')) {
        loadReceivableData();
    }
});