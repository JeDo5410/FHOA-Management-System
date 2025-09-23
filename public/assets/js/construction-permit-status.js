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

// Function to load permit status counts for all filter types
function loadPermitStatusCounts() {
    const countElements = {
        all: document.getElementById('allPermitsCount'),
        1: document.getElementById('ongoingPermitsCount'),
        2: document.getElementById('inspectionPermitsCount'),
        3: document.getElementById('bondReleasePermitsCount'),
        4: document.getElementById('forfeitedPermitsCount'),
        5: document.getElementById('releasedPermitsCount')
    };
    
    // Set loading state
    Object.values(countElements).forEach(el => {
        if (el) el.textContent = 'Loading...';
    });
    
    // Fetch counts for all filter types
    ['all', '1', '2', '3', '4', '5'].forEach(status => {
        fetch(`/construction-permit/get-permit-status-data?status=${status}&count_only=1`)
            .then(response => response.json())
            .then(data => {
                const count = Array.isArray(data) ? data.length : (data.count || 0);
                const element = countElements[status];
                if (element) {
                    element.textContent = `(${count})`;
                }
            })
            .catch(error => {
                console.error(`Error loading ${status} permit count:`, error);
                const element = countElements[status];
                if (element) {
                    element.textContent = '(Error)';
                }
            });
    });
}

// Function to load permit status data with filter
function loadPermitStatusData(status) {
    // Show loading indicator
    const tbody = document.querySelector('#permitStatusTable tbody');
    tbody.innerHTML = '<tr><td colspan="25" class="text-center">Loading data...</td></tr>';
    
    // Fetch data from server
    fetch(`/construction-permit/get-permit-status-data?status=${status}`)
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
                tbody.innerHTML = '<tr><td colspan="25" class="text-center">No data found</td></tr>';
                showToast('info', 'No permit records found');
                
                // Update current record count badge to 0
                const currentCountElement = document.getElementById('currentPermitCount');
                if (currentCountElement) {
                    currentCountElement.textContent = '0';
                }
                return;
            }
            
            // Show count notification
            const statusText = status === 'all' ? 'total' : getStatusText(status);
            showToast('success', `Found ${data.length} ${statusText} permit records`);
            
            // Update current record count badge
            const currentCountElement = document.getElementById('currentPermitCount');
            if (currentCountElement) {
                currentCountElement.textContent = data.length;
            }
            
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
                `;
                
                tbody.appendChild(row);
            });
            
            // Update scrollbar
            updatePermitScrollbarWidth();
        })
        .catch(error => {
            console.error('Error loading permit status data:', error);
            showToast('error', 'Failed to load permit status data. Please try again.');
            tbody.innerHTML = '<tr><td colspan="25" class="text-center text-danger">Error loading data</td></tr>';
            
            // Update current record count badge to 0 on error
            const currentCountElement = document.getElementById('currentPermitCount');
            if (currentCountElement) {
                currentCountElement.textContent = '0';
            }
        });
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

// Initialize permit status functionality when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Only initialize if we're on the construction permit page and permit status elements exist
    if (document.getElementById('permitStatusTable')) {
        // Load permit status counts
        loadPermitStatusCounts();
        
        // Setup scroll sync
        setupPermitScrollSync();
        
        // Update scrollbar on window resize
        window.addEventListener('resize', updatePermitScrollbarWidth);
        
        // Load initial data (all permits)
        loadPermitStatusData('all');
        
        // Handle download button click
        const downloadBtn = document.getElementById('downloadPermitBtn');
        if (downloadBtn) {
            downloadBtn.addEventListener('click', function() {
                const selectedStatus = document.querySelector('input[name="permitStatus"]:checked').value;
                
                // Create download URL with filters
                let downloadUrl = '/construction-permit/download/permit-status-data';
                downloadUrl += '?status=' + selectedStatus;
                
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
    }
});