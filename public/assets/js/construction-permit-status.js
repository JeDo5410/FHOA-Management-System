// Function to update scrollbar width for permit status table
function updatePermitScrollbarWidth() {
    const tableContainer = document.getElementById('permitStatusTableContainer');
    const table = tableContainer ? tableContainer.querySelector('table') : null;
    const scrollbarContent = document.getElementById('permitScrollbarContent');
    
    if (table && scrollbarContent && tableContainer) {
        // Use scrollWidth instead of offsetWidth to get the full scrollable width
        scrollbarContent.style.width = (table.scrollWidth + 10) + 'px';
        
        // Force a repaint of the scrollbar
        tableContainer.scrollLeft = tableContainer.scrollLeft;
    }
}

// Function to setup scroll sync for permit status table
function setupPermitScrollSync() {
    const tableContainer = document.getElementById('permitStatusTableContainer');
    const stickyScrollbar = document.getElementById('permitStickyScrollbar');
    
    if (tableContainer && stickyScrollbar) {
        // Remove any existing scroll listeners
        tableContainer.onscroll = null;
        stickyScrollbar.onscroll = null;
        
        // Add scroll listener to table container
        tableContainer.onscroll = function() {
            stickyScrollbar.scrollLeft = tableContainer.scrollLeft;
        };
        
        // Add scroll listener to sticky scrollbar
        stickyScrollbar.onscroll = function() {
            tableContainer.scrollLeft = stickyScrollbar.scrollLeft;
        };
    }
}

// Function to handle filter type changes
function handleFilterTypeChange() {
    const filterType = document.querySelector('input[name="permitFilter"]:checked').value;
    
    // Hide all input groups
    document.getElementById('permitIdGroup').style.display = 'none';
    document.getElementById('addressIdGroup').style.display = 'none';
    document.getElementById('statusGroup').style.display = 'none';
    
    // Disable all inputs
    document.getElementById('permitIdInput').disabled = true;
    document.getElementById('permitIdSearchBtn').disabled = true;
    document.getElementById('addressIdInput').disabled = true;
    document.getElementById('addressIdSearchBtn').disabled = true;
    document.getElementById('statusDropdown').disabled = true;
    
    // Clear all inputs
    document.getElementById('permitIdInput').value = '';
    document.getElementById('addressIdInput').value = '';
    document.getElementById('statusDropdown').value = '';
    
    // Show and enable appropriate inputs based on selected filter
    switch(filterType) {
        case 'all':
            loadAllPermits();
            break;
        case 'permit_id':
            document.getElementById('permitIdGroup').style.display = '';
            document.getElementById('permitIdInput').disabled = false;
            document.getElementById('permitIdSearchBtn').disabled = false;
            break;
        case 'address_id':
            document.getElementById('addressIdGroup').style.display = '';
            document.getElementById('addressIdInput').disabled = false;
            document.getElementById('addressIdSearchBtn').disabled = false;
            break;
        case 'status':
            document.getElementById('statusGroup').style.display = '';
            document.getElementById('statusDropdown').disabled = false;
            break;
    }
}

// Function to load permit status counts
function loadPermitStatusCounts() {
    fetch('/construction-permit/status-counts')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                updateStatusCountsDisplay(data.total_count, data.status_counts);
            } else {
                console.error('Failed to load status counts:', data.message);
                updateStatusCountsDisplay(0, []);
            }
        })
        .catch(error => {
            console.error('Error loading status counts:', error);
            updateStatusCountsDisplay(0, []);
        });
}

// Function to update status counts display
function updateStatusCountsDisplay(totalCount, statusCounts) {
    const container = document.getElementById('statusCountsContainer');
    if (!container) return;
    
    // Clear existing content
    container.innerHTML = '';
    
    // Add total count badge
    const totalBadge = document.createElement('span');
    totalBadge.className = 'badge bg-dark fw-bold';
    totalBadge.innerHTML = `<i class="bi bi-clipboard-data me-1"></i>Total: ${totalCount}`;
    container.appendChild(totalBadge);
    
    // Add status count badges
    statusCounts.forEach(status => {
        const badge = document.createElement('span');
        badge.className = `badge bg-${status.color} status-count-badge`;
        badge.setAttribute('data-status-id', status.status_id);
        badge.innerHTML = `${status.status_name}: ${status.count}`;
        badge.style.cursor = 'pointer';
        badge.title = `Click to filter by ${status.status_name}`;
        
        // Add click handler to filter by status
        badge.addEventListener('click', function() {
            // Select the status filter radio button
            document.getElementById('filterStatus').checked = true;
            
            // Show status dropdown and set value
            handleFilterTypeChange();
            document.getElementById('statusDropdown').value = status.status_id;
            
            // Load filtered data
            filterByStatus();
        });
        
        container.appendChild(badge);
    });
}

// Function to load all permits
function loadAllPermits() {
    loadPermitData('all', {});
}

// Function to search by permit ID
function searchByPermitId() {
    const permitId = document.getElementById('permitIdInput').value.trim();
    if (!permitId) {
        showToast('error', 'Please enter a permit number');
        return;
    }
    loadPermitData('permit_id', { permit_id: permitId });
}

// Function to search by address ID
function searchByAddressId() {
    const addressId = document.getElementById('addressIdInput').value.trim();
    if (!addressId) {
        showToast('error', 'Please enter an address ID');
        return;
    }
    if (addressId.length !== 5 || !/^\d{5}$/.test(addressId)) {
        showToast('error', 'Address ID must be exactly 5 digits');
        return;
    }
    loadPermitData('address_id', { address_id: addressId });
}

// Function to filter by status
function filterByStatus() {
    const status = document.getElementById('statusDropdown').value;
    if (!status) {
        showToast('error', 'Please select a status');
        return;
    }
    loadPermitData('status', { status: status });
}

// Function to load permit data with various filters
function loadPermitData(filterType, params) {
    // Show loading indicator
    const tbody = document.querySelector('#permitStatusTable tbody');
    tbody.innerHTML = '<tr><td colspan="26" class="text-center">Loading data...</td></tr>';
    
    // Build query parameters
    let queryParams = new URLSearchParams();
    queryParams.set('filter_type', filterType);
    Object.keys(params).forEach(key => {
        if (params[key]) queryParams.set(key, params[key]);
    });
    
    // Fetch data from server
    fetch(`/construction-permit/get-permit-status-data?${queryParams.toString()}`)
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
                tbody.innerHTML = '<tr><td colspan="26" class="text-center">No data found</td></tr>';
                showToast('info', 'No permit records found');
                
                return;
            }
            
            // Show count notification
            const filterText = getFilterText(filterType, params);
            showToast('success', `Found ${data.length} ${filterText} permit records`);
            
            // Add rows
            data.forEach(permit => {
                const row = document.createElement('tr');
                
                // Format date fields
                const permitStartDate = permit['Permit Start Date'] ? new Date(permit['Permit Start Date']).toLocaleDateString() : '';
                const permitEndDate = permit['Permit End Date'] ? new Date(permit['Permit End Date']).toLocaleDateString() : '';
                const applicationDate = permit['ApplicationDate'] ? new Date(permit['ApplicationDate']).toLocaleDateString() : '';
                const sinDate = permit['SIN Date'] ? new Date(permit['SIN Date']).toLocaleDateString() : '';
                const bondDate = permit['Bond Date'] ? new Date(permit['Bond Date']).toLocaleDateString() : '';
                const inspectionDate = permit['Inspection Date'] ? new Date(permit['Inspection Date']).toLocaleDateString() : '';
                const bondReleaseDate = permit['Bond Release Date'] ? new Date(permit['Bond Release Date']).toLocaleDateString() : '';
                const timeEnter = permit['Time Enter'] ? new Date(permit['Time Enter']).toLocaleString() : '';
                
                // Format currency fields
                const formatter = new Intl.NumberFormat('en-PH', {
                    style: 'currency',
                    currency: 'PHP',
                    minimumFractionDigits: 2
                });
                
                const feeAmount = permit['Fee Amt.'] ? formatter.format(permit['Fee Amt.']) : '';
                const bondAmount = permit['Bond Amt.'] ? formatter.format(permit['Bond Amt.']) : '';
                
                row.innerHTML = `
                    <td>${permit['Permit No.'] || ''}</td>
                    <td>${permit['Permit Type'] || ''}</td>
                    <td>${permit['Permit Status'] || ''}</td>
                    <td>${permitStartDate}</td>
                    <td>${permitEndDate}</td>
                    <td>${permit['HOA Address ID.'] || ''}</td>
                    <td>${permit['HOA Name'] || ''}</td>
                    <td>${applicationDate}</td>
                    <td>${permit['Applicant Name'] || ''}</td>
                    <td>${permit['Applicant Contact'] || ''}</td>
                    <td>${permit['Contractor Name'] || ''}</td>
                    <td>${permit['Contractor Contact'] || ''}</td>
                    <td>${permit['Payment SIN'] || ''}</td>
                    <td>${sinDate}</td>
                    <td>${feeAmount}</td>
                    <td>${permit['Bond ARN'] || ''}</td>
                    <td>${bondAmount}</td>
                    <td>${bondDate}</td>
                    <td>${permit['Inspector'] || ''}</td>
                    <td>${inspectionDate}</td>
                    <td>${permit['Inspector Note'] || ''}</td>
                    <td>${permit['Bond Release Type'] || ''}</td>
                    <td>${permit['Bond Receiver'] || ''}</td>
                    <td>${bondReleaseDate}</td>
                    <td>${permit['Remarks'] || ''}</td>
                    <td>${timeEnter}</td>
                `;
                
                tbody.appendChild(row);
            });
            
            // Update scrollbar
            updatePermitScrollbarWidth();
        })
        .catch(error => {
            console.error('Error loading permit data:', error);
            showToast('error', 'Failed to load permit data. Please try again.');
            tbody.innerHTML = '<tr><td colspan="26" class="text-center text-danger">Error loading data</td></tr>';
        });
}

// Function to get filter text for notifications
function getFilterText(filterType, params) {
    switch(filterType) {
        case 'all': return '';
        case 'permit_id': return 'matching permit ID';
        case 'address_id': return 'for address';
        case 'status': return getStatusText(params.status);
        default: return '';
    }
}

// Function to get status text from status code
function getStatusText(statusCode) {
    const statusMap = {
        '1': 'on-going',
        '2': 'for inspection',
        '3': 'for bond release', 
        '4': 'closed (forfeited)',
        '5': 'closed (released)'
    };
    return statusMap[statusCode] || 'status';
}

// Function to validate address ID input (5 digits only)
function validateAddressIdInput(input) {
    // Remove any non-digit characters
    input.value = input.value.replace(/\D/g, '');
    
    // Limit to 5 digits
    if (input.value.length > 5) {
        input.value = input.value.substring(0, 5);
    }
}

// Initialize permit status functionality when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Only initialize if we're on the construction permit page and permit status elements exist
    if (document.getElementById('permitStatusTable')) {
        
        // Setup scroll sync
        setupPermitScrollSync();
        
        // Update scrollbar on window resize
        window.addEventListener('resize', updatePermitScrollbarWidth);
        
        // Handle filter type changes
        document.querySelectorAll('input[name="permitFilter"]').forEach(radio => {
            radio.addEventListener('change', handleFilterTypeChange);
        });
        
        // Handle search button clicks
        document.getElementById('permitIdSearchBtn').addEventListener('click', searchByPermitId);
        document.getElementById('addressIdSearchBtn').addEventListener('click', searchByAddressId);
        
        // Handle Enter key press in input fields
        document.getElementById('permitIdInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchByPermitId();
            }
        });
        
        document.getElementById('addressIdInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchByAddressId();
            }
        });
        
        // Handle address ID input validation
        document.getElementById('addressIdInput').addEventListener('input', function() {
            validateAddressIdInput(this);
        });
        
        // Handle status dropdown change
        document.getElementById('statusDropdown').addEventListener('change', filterByStatus);
        
        // Handle download button click
        const downloadBtn = document.getElementById('downloadPermitBtn');
        if (downloadBtn) {
            downloadBtn.addEventListener('click', function() {
                const filterType = document.querySelector('input[name="permitFilter"]:checked').value;
                let downloadUrl = '/construction-permit/download/permit-status-data';
                let queryParams = new URLSearchParams();
                
                queryParams.set('filter_type', filterType);
                
                switch(filterType) {
                    case 'permit_id':
                        const permitId = document.getElementById('permitIdInput').value.trim();
                        if (permitId) queryParams.set('permit_id', permitId);
                        break;
                    case 'address_id':
                        const addressId = document.getElementById('addressIdInput').value.trim();
                        if (addressId) queryParams.set('address_id', addressId);
                        break;
                    case 'status':
                        const status = document.getElementById('statusDropdown').value;
                        if (status) queryParams.set('status', status);
                        break;
                }
                
                downloadUrl += '?' + queryParams.toString();
                
                // Redirect to download URL
                window.location.href = downloadUrl;
            });
        }
        
        // Handle tab switching to permit status
        const permitHistoryTab = document.getElementById('permit-history-tab');
        if (permitHistoryTab) {
            permitHistoryTab.addEventListener('shown.bs.tab', function() {
                // Refresh scrollbar and sync when tab is shown
                setTimeout(function() {
                    updatePermitScrollbarWidth();
                    setupPermitScrollSync();
                }, 100);
            });
        }
        
        // Load initial status counts
        loadPermitStatusCounts();
        
        // Load initial data (all permits)
        loadAllPermits();
    }
});