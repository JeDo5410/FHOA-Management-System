// Function to update scrollbar width
function updateScrollbarWidth() {
    const activeTableContainer = document.querySelector('.table-responsive:not([style*="display: none"])');
    const activeTable = activeTableContainer ? activeTableContainer.querySelector('table') : null;
    const scrollbarContent = document.getElementById('scrollbarContent');
    
    if (activeTable && scrollbarContent && activeTableContainer) {
        // Use scrollWidth instead of offsetWidth to get the full scrollable width
        // adding a small buffer (10px) to ensure we can scroll all the way
        scrollbarContent.style.width = (activeTable.scrollWidth + 10) + 'px';
        
        // Force a repaint of the scrollbar
        activeTableContainer.scrollLeft = activeTableContainer.scrollLeft;
    }
}


document.addEventListener('DOMContentLoaded', function() {        
    updateScrollbarWidth();
    window.addEventListener('resize', updateScrollbarWidth);
    
            // Set up scroll sync for BOTH tables
    function setupScrollSync() {
        const visibleTableContainer = document.querySelector('.table-responsive:not([style*="display: none"])');
        const stickyScrollbar = document.getElementById('stickyScrollbar');
        
        if (visibleTableContainer && stickyScrollbar) {
            // Remove any existing scroll listeners from all table containers
            document.querySelectorAll('.table-responsive').forEach(container => {
                container.onscroll = null;
            });
            
            // Add scroll listener to currently visible container
            visibleTableContainer.onscroll = function() {
                stickyScrollbar.scrollLeft = visibleTableContainer.scrollLeft;
            };
            
            // Add scroll listener to sticky scrollbar
            stickyScrollbar.onscroll = function() {
                visibleTableContainer.scrollLeft = stickyScrollbar.scrollLeft;
            };
        }
    }
    
    // Run initial setup
    setupScrollSync();
    
    // Override switchDataView to call setupScrollSync
    const originalSwitchDataView = window.switchDataView;
    window.switchDataView = function(view) {
        originalSwitchDataView(view);
        
        // After switching views, update scrollbar and reset scroll sync
        setTimeout(function() {
            updateScrollbarWidth();
            setupScrollSync();
        }, 100);
    };
    
    // Initialize with Member's Data view
    switchDataView('members');

    // Sync scroll positions
    const mainTableContainer = document.querySelector('.table-responsive');
    const stickyScrollbar = document.getElementById('stickyScrollbar');
    
    if (mainTableContainer && stickyScrollbar) {
        mainTableContainer.addEventListener('scroll', function() {
            stickyScrollbar.scrollLeft = mainTableContainer.scrollLeft;
        });
        
        stickyScrollbar.addEventListener('scroll', function() {
            mainTableContainer.scrollLeft = stickyScrollbar.scrollLeft;
        });
    }
    
    // Initialize with Member's Data view
    switchDataView('members');
});

// Function to switch between data views
function switchDataView(view) {
    const memberDataBtn = document.getElementById('membersDataBtn');
    const carStickerBtn = document.getElementById('carStickerBtn');
    const memberDataTableContainer = document.getElementById('memberDataTableContainer');
    const carStickerTableContainer = document.getElementById('carStickerTableContainer');
    const memberStatusFilter = document.getElementById('memberStatusFilter');
    const statusAllRadio = document.getElementById('statusAll');
    
    if (view === 'members') {
        // Show Member Data, hide Car Sticker
        memberDataBtn.classList.add('active');
        memberDataBtn.classList.remove('btn-outline-primary');
        memberDataBtn.classList.add('btn-primary');
        
        carStickerBtn.classList.remove('active');
        carStickerBtn.classList.remove('btn-primary');
        carStickerBtn.classList.add('btn-outline-primary');
        
        memberDataTableContainer.style.display = '';
        carStickerTableContainer.style.display = 'none';
        memberStatusFilter.style.display = '';
        
        // Check if we're switching back to members view (not initial load)
        if (document.querySelector('input[name="memberStatus"]:checked').value !== 'all') {
            // Set 'All Members' radio button as checked without triggering its onclick
            statusAllRadio.checked = true;
            // Load member data with 'all' filter
            loadMemberData('all');
        } else {
            // Initial load or already on 'all' - use existing filter
            loadMemberData('all');
        }
    } else {
        // Show Car Sticker, hide Member Data
        carStickerBtn.classList.add('active');
        carStickerBtn.classList.remove('btn-outline-primary');
        carStickerBtn.classList.add('btn-primary');
        
        memberDataBtn.classList.remove('active');
        memberDataBtn.classList.remove('btn-primary');
        memberDataBtn.classList.add('btn-outline-primary');
        
        memberDataTableContainer.style.display = 'none';
        carStickerTableContainer.style.display = '';
        memberStatusFilter.style.display = 'none';
        
        // Load car sticker data
        loadCarStickerData();
    }
    
    // Update scrollbar
    setTimeout(function() {
        updateScrollbarWidth();
    }, 100);
}

// Function to load member data with filter
function loadMemberData(status) {
    // Show loading indicator
    const tbody = document.querySelector('#memberDataTable tbody');
    tbody.innerHTML = '<tr><td colspan="38" class="text-center">Loading data...</td></tr>';
    
    // Fetch data from server
    fetch(`/reports/get-members-data?status=${status}`)
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
                tbody.innerHTML = '<tr><td colspan="38" class="text-center">No data found</td></tr>';
                showToast('info', 'No records found');
                return;
            }
            
            // Show count notification
            const statusText = status === 'all' ? 'total' : status;
            showToast('success', `Found ${data.length} ${statusText} member records`);
            
            // Add rows
            data.forEach(member => {
                const row = document.createElement('tr');
                
                // Format date fields
                const arrearMonth = member.arrear_month ? new Date(member.arrear_month).toLocaleDateString() : 'N/A';
                const lastPayDate = member.last_paydate ? new Date(member.last_paydate).toLocaleDateString() : 'N/A';
                const memDate = member.mem_date ? new Date(member.mem_date).toLocaleDateString() : 'N/A';
                
                // Format currency fields
                const formatter = new Intl.NumberFormat('en-PH', {
                    style: 'currency',
                    currency: 'PHP',
                    minimumFractionDigits: 2
                });
                
                const monthlydues = member.mem_monthlydues ? formatter.format(member.mem_monthlydues) : '₱0.00';
                const arrear = member.arrear ? formatter.format(member.arrear) : '₱0.00';
                const arrearInterest = member.arrear_interest ? formatter.format(member.arrear_interest) : '₱0.00';
                const lastPayAmount = member.last_payamount ? formatter.format(member.last_payamount) : '₱0.00';
                
                row.innerHTML = `
                    <td>${member.mem_id || ''}</td>
                    <td>${member.mem_transno || ''}</td>
                    <td>${member.mem_add_id || ''}</td>
                    <td>${member.mem_name || ''}</td>
                    <td>${member.mem_SPA_Tenant || ''}</td>
                    <td>${member.mem_type || ''}</td>
                    <td>${monthlydues}</td>
                    <td>${arrearMonth}</td>
                    <td>${arrear}</td>
                    <td>${member.arrear_count || '0'}</td>
                    <td>${arrearInterest}</td>
                    <td>${member.last_or || ''}</td>
                    <td>${lastPayDate}</td>
                    <td>${lastPayAmount}</td>
                    <td>${member.mem_mobile || ''}</td>
                    <td>${memDate}</td>
                    <td>${member.mem_email || ''}</td>
                    <td>${member.mem_Resident1 || ''}</td>
                    <td>${member.mem_Resident2 || ''}</td>
                    <td>${member.mem_Resident3 || ''}</td>
                <td>${member.mem_Resident4 || ''}</td>
                <td>${member.mem_Resident5 || ''}</td>
                <td>${member.mem_Resident6 || ''}</td>
                <td>${member.mem_Resident7 || ''}</td>
                <td>${member.mem_Resident8 || ''}</td>
                <td>${member.mem_Resident9 || ''}</td>
                <td>${member.mem_Resident10 || ''}</td>
                <td>${member.mem_Relationship1 || ''}</td>
                <td>${member.mem_Relationship2 || ''}</td>
                <td>${member.mem_Relationship3 || ''}</td>
                <td>${member.mem_Relationship4 || ''}</td>
                <td>${member.mem_Relationship5 || ''}</td>
                <td>${member.mem_Relationship6 || ''}</td>
                <td>${member.mem_Relationship7 || ''}</td>
                <td>${member.mem_Relationship8 || ''}</td>
                <td>${member.mem_Relationship9 || ''}</td>
                <td>${member.mem_Relationship10 || ''}</td>
                <td>${member.mem_remarks || ''}</td>
            `;
            
            tbody.appendChild(row);
        });
        
        // Update scrollbar
        updateScrollbarWidth();
    })
    .catch(error => {
        console.error('Error loading member data:', error);
        showToast('error', 'Failed to load member data. Please try again.');
        tbody.innerHTML = '<tr><td colspan="38" class="text-center text-danger">Error loading data</td></tr>';
    });
}


// Function to load car sticker data
function loadCarStickerData() {
    // Show loading indicator
    const tbody = document.querySelector('#carStickerTable tbody');
    tbody.innerHTML = '<tr><td colspan="14" class="text-center">Loading data...</td></tr>';
    
    // Fetch data from server
    fetch('/reports/get-car-sticker-data')
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
                showToast('info', 'No vehicle records found');
                return;
            }
            
            // Show count notification
            showToast('success', `Found ${data.length} vehicle records`);
            
            // Add rows
            data.forEach(car => {
                const row = document.createElement('tr');
                
                // Format active status
                const active = car.vehicle_active === 0 ? 'Yes' : 'No';
                
                row.innerHTML = `
                    <td>${car.mem_id || ''}</td>
                    <td>${car.mem_add_id || ''}</td>
                    <td>${car.mem_typedescription || ''}</td>
                    <td>${car.mem_name || ''}</td>
                    <td>${car.mem_SPA_Tenant || ''}</td>
                    <td>${car.vehicle_maker || ''}</td>
                    <td>${car.vehicle_type || ''}</td>
                    <td>${car.vehicle_color || ''}</td>
                    <td>${car.vehicle_OR || ''}</td>
                    <td>${car.vehicle_CR || ''}</td>
                    <td>${car.vehicle_plate || ''}</td>
                    <td>${car.car_sticker || ''}</td>
                    <td>${active}</td>
                    <td>${car.remarks || ''}</td>
                `;
                
                tbody.appendChild(row);
            });
            
            // Update scrollbar
            updateScrollbarWidth();
        })
        .catch(error => {
            console.error('Error loading car sticker data:', error);
            showToast('error', 'Failed to load car sticker data. Please try again.');
            tbody.innerHTML = '<tr><td colspan="14" class="text-center text-danger">Error loading data</td></tr>';
        });
}

// Download CSV
document.getElementById('downloadBtn').addEventListener('click', function() {
    const view = document.getElementById('membersDataBtn').classList.contains('active') ? 'members' : 'cars';
    let memberStatus = 'all';
    
    if (view === 'members') {
        memberStatus = document.querySelector('input[name="memberStatus"]:checked').value;
    }
    
    // Create download URL with filters
    let downloadUrl = view === 'members' 
        ? '/reports/download/members-data' 
        : '/reports/download/car-sticker';
    
    // Add filter parameters
    if (view === 'members') {
        downloadUrl += '?status=' + memberStatus;
    }
    
    // Redirect to download URL
    window.location.href = downloadUrl;
});
