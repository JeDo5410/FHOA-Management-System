// Global Tabulator instance for the permit status table
var permitTable;

// Function to handle filter type changes
function handleFilterTypeChange() {
    const checkedRadio = document.querySelector('input[name="permitFilter"]:checked');
    const filterType = checkedRadio ? checkedRadio.value : null;

    // Reset all badges to blue when a radio button is selected
    if (filterType) {
        document.querySelectorAll('.status-count-badge').forEach(b => {
            b.classList.remove('bg-dark');
            b.classList.add('bg-primary');
        });
    }

    // Disable all inputs
    document.getElementById('permitIdInput').disabled = true;
    document.getElementById('permitIdSearchBtn').disabled = true;
    document.getElementById('addressIdInput').disabled = true;
    document.getElementById('addressIdSearchBtn').disabled = true;

    // Clear all inputs
    document.getElementById('permitIdInput').value = '';
    document.getElementById('addressIdInput').value = '';

    // Enable appropriate inputs based on selected filter
    if (filterType === 'permit_id') {
        document.getElementById('permitIdInput').disabled = false;
        document.getElementById('permitIdSearchBtn').disabled = false;
    } else if (filterType === 'address_id') {
        document.getElementById('addressIdInput').disabled = false;
        document.getElementById('addressIdSearchBtn').disabled = false;
    }
}

// Function to load permit status counts
function loadPermitStatusCounts() {
    fetch('/construction-permit/status-counts')
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            if (data.success) {
                updateStatusCountsDisplay(data.total_count, data.status_counts);
            } else {
                updateStatusCountsDisplay(0, []);
            }
        })
        .catch(() => updateStatusCountsDisplay(0, []));
}

// Function to update status counts display
function updateStatusCountsDisplay(totalCount, statusCounts) {
    const container = document.getElementById('statusCountsContainer');
    if (!container) return;

    container.innerHTML = '';

    const allBadge = document.createElement('span');
    allBadge.className = 'badge bg-primary fw-bold status-count-badge';
    allBadge.setAttribute('data-badge-type', 'all');
    allBadge.innerHTML = `<i class="bi bi-clipboard-data me-1"></i>All: ${totalCount}`;
    allBadge.title = 'Click to show all permits';
    allBadge.style.cursor = 'pointer';

    allBadge.addEventListener('click', function() {
        document.querySelectorAll('.status-count-badge').forEach(b => {
            b.classList.remove('bg-dark');
            b.classList.add('bg-primary');
        });
        this.classList.remove('bg-primary');
        this.classList.add('bg-dark');

        const permitIdRadio = document.getElementById('filterPermitId');
        const addressIdRadio = document.getElementById('filterAddressId');
        if (permitIdRadio) permitIdRadio.checked = false;
        if (addressIdRadio) addressIdRadio.checked = false;
        handleFilterTypeChange();

        loadAllPermits();
    });
    container.appendChild(allBadge);

    statusCounts.forEach(status => {
        const badge = document.createElement('span');
        const isForInspection = status.status_id === 2;
        badge.className = `badge ${isForInspection ? 'bg-dark' : 'bg-primary'} status-count-badge`;
        badge.setAttribute('data-status-id', status.status_id);
        badge.setAttribute('data-badge-type', 'status');
        badge.innerHTML = `${status.status_name}: ${status.count}`;
        badge.style.cursor = 'pointer';
        badge.title = `Click to filter by ${status.status_name}`;

        badge.addEventListener('click', function() {
            document.querySelectorAll('.status-count-badge').forEach(b => {
                b.classList.remove('bg-dark');
                b.classList.add('bg-primary');
            });
            this.classList.remove('bg-primary');
            this.classList.add('bg-dark');

            const permitIdRadio = document.getElementById('filterPermitId');
            const addressIdRadio = document.getElementById('filterAddressId');
            if (permitIdRadio) permitIdRadio.checked = false;
            if (addressIdRadio) addressIdRadio.checked = false;
            handleFilterTypeChange();

            loadPermitData('status', { status: status.status_id });
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

// Function to load permit data with various filters
function loadPermitData(filterType, params) {
    if (permitTable) {
        permitTable.alert('Loading...', 'msg');
    }

    let queryParams = new URLSearchParams();
    queryParams.set('filter_type', filterType);
    Object.keys(params).forEach(key => {
        if (params[key] !== undefined && params[key] !== null) {
            queryParams.set(key, params[key]);
        }
    });

    fetch(`/construction-permit/get-permit-status-data?${queryParams.toString()}`)
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            if (!permitTable) return;
            permitTable.clearAlert();

            if (data.length === 0) {
                permitTable.setData([]);
                showToast('info', 'No permit records found');
                return;
            }

            const formatter = new Intl.NumberFormat('en-PH', {
                style: 'currency',
                currency: 'PHP',
                minimumFractionDigits: 2
            });

            const tableData = data.map(permit => ({
                no: permit['No.'] || '',
                permit_no: permit['Permit No.'] || '',
                permit_type: permit['Permit Type'] || '',
                permit_status: permit['Permit Status'] || '',
                permit_start_date: permit['Permit Start Date'] ? new Date(permit['Permit Start Date']).toLocaleDateString() : '',
                permit_end_date: permit['Permit End Date'] ? new Date(permit['Permit End Date']).toLocaleDateString() : '',
                hoa_address_id: permit['HOA Address ID.'] || '',
                hoa_name: permit['HOA Name'] || '',
                application_date: permit['ApplicationDate'] ? new Date(permit['ApplicationDate']).toLocaleDateString() : '',
                applicant_name: permit['Applicant Name'] || '',
                applicant_contact: permit['Applicant Contact'] || '',
                contractor_name: permit['Contractor Name'] || '',
                contractor_contact: permit['Contractor Contact'] || '',
                payment_sin: permit['Payment SIN'] || '',
                sin_date: permit['SIN Date'] ? new Date(permit['SIN Date']).toLocaleDateString() : '',
                fee_amount: permit['Fee Amt.'] ? formatter.format(permit['Fee Amt.']) : '',
                bond_arn: permit['Bond ARN'] || '',
                bond_amount: permit['Bond Amt.'] ? formatter.format(permit['Bond Amt.']) : '',
                bond_date: permit['Bond Date'] ? new Date(permit['Bond Date']).toLocaleDateString() : '',
                inspector: permit['Inspector'] || '',
                inspection_date: permit['Inspection Date'] ? new Date(permit['Inspection Date']).toLocaleDateString() : '',
                inspector_note: permit['Inspector Note'] || '',
                bond_release_type: permit['Bond Release Type'] || '',
                bond_receiver: permit['Bond Receiver'] || '',
                bond_release_date: permit['Bond Release Date'] ? new Date(permit['Bond Release Date']).toLocaleDateString() : '',
                remarks: permit['Remarks'] || '',
                user_fullname: permit['User Fullname'] || '',
                time_entry: permit['Time Entry'] ? new Date(permit['Time Entry']).toLocaleString() : '',
                // Internal fields used by rowFormatter
                _statuscode: permit.statuscode,
                _inspection_form: permit.inspection_form,
            }));

            permitTable.setData(tableData);

            const filterText = getFilterText(filterType, params);
            showToast('success', `Found ${data.length} ${filterText} permit records`);
        })
        .catch(error => {
            console.error('Error loading permit data:', error);
            if (permitTable) permitTable.clearAlert();
            showToast('error', 'Failed to load permit data. Please try again.');
        });
}

// Function to get filter text for notifications
function getFilterText(filterType, params) {
    switch (filterType) {
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
    return statusMap[String(statusCode)] || 'status';
}

// Function to validate address ID input (5 digits only)
function validateAddressIdInput(input) {
    input.value = input.value.replace(/\D/g, '');
    if (input.value.length > 5) {
        input.value = input.value.substring(0, 5);
    }
}

// Initialize permit status functionality when DOM is loaded
document.addEventListener('DOMContentLoaded', function () {
    if (!document.getElementById('permitStatusTable')) return;

    // Initialize Tabulator
    permitTable = new Tabulator('#permitStatusTable', Object.assign({}, window.FHOA_TABLE_DEFAULTS, {
        height: '65vh',
        rowFormatter: function (row) {
            var data = row.getData();
            if (data._statuscode == 2 && (!data._inspection_form || data._inspection_form == 0)) {
                row.getElement().classList.add('needs-inspection');
            }
        },
        columns: [
            { title: 'No.',               field: 'no',               width: 60,  frozen: true },
            { title: 'Permit No.',         field: 'permit_no',        width: 120, frozen: true },
            { title: 'Permit Type',        field: 'permit_type',      width: 130 },
            { title: 'Permit Status',      field: 'permit_status',    width: 140 },
            { title: 'Permit Start Date',  field: 'permit_start_date',width: 140 },
            { title: 'Permit End Date',    field: 'permit_end_date',  width: 130 },
            { title: 'HOA Address ID',     field: 'hoa_address_id',   width: 130 },
            { title: 'HOA Name',           field: 'hoa_name',         width: 200, minWidth: 150 },
            { title: 'Application Date',   field: 'application_date', width: 140 },
            { title: 'Applicant Name',     field: 'applicant_name',   width: 180 },
            { title: 'Applicant Contact',  field: 'applicant_contact',width: 150 },
            { title: 'Contractor Name',    field: 'contractor_name',  width: 180 },
            { title: 'Contractor Contact', field: 'contractor_contact',width: 155 },
            { title: 'Payment SIN',        field: 'payment_sin',      width: 130 },
            { title: 'SIN Date',           field: 'sin_date',         width: 120 },
            { title: 'Fee Amount',         field: 'fee_amount',       width: 140, hozAlign: 'right' },
            { title: 'Bond ARN',           field: 'bond_arn',         width: 120 },
            { title: 'Bond Amount',        field: 'bond_amount',      width: 140, hozAlign: 'right' },
            { title: 'Bond Date',          field: 'bond_date',        width: 120 },
            { title: 'Inspector',          field: 'inspector',        width: 160 },
            { title: 'Inspection Date',    field: 'inspection_date',  width: 135 },
            { title: 'Inspector Note',     field: 'inspector_note',   width: 180 },
            { title: 'Bond Release Type',  field: 'bond_release_type',width: 155 },
            { title: 'Bond Receiver',      field: 'bond_receiver',    width: 150 },
            { title: 'Bond Release Date',  field: 'bond_release_date',width: 145 },
            { title: 'Remarks',            field: 'remarks',          width: 200, minWidth: 120 },
            { title: 'User Fullname',      field: 'user_fullname',    width: 180 },
            { title: 'Time Entry',         field: 'time_entry',       width: 175 },
        ],
    }));

    // Handle filter type radio changes
    document.querySelectorAll('input[name="permitFilter"]').forEach(radio => {
        radio.addEventListener('change', handleFilterTypeChange);
    });

    // Handle search button clicks
    document.getElementById('permitIdSearchBtn').addEventListener('click', searchByPermitId);
    document.getElementById('addressIdSearchBtn').addEventListener('click', searchByAddressId);

    // Handle Enter key in filter inputs
    document.getElementById('permitIdInput').addEventListener('keypress', function (e) {
        if (e.key === 'Enter') searchByPermitId();
    });
    document.getElementById('addressIdInput').addEventListener('keypress', function (e) {
        if (e.key === 'Enter') searchByAddressId();
    });

    // Address ID validation
    document.getElementById('addressIdInput').addEventListener('input', function () {
        validateAddressIdInput(this);
    });

    // Download button — use Tabulator's built-in CSV export
    const downloadBtn = document.getElementById('downloadPermitBtn');
    if (downloadBtn) {
        downloadBtn.addEventListener('click', function () {
            if (permitTable) {
                permitTable.download('csv', 'construction-permits.csv');
            }
        });
    }

    // Load initial status counts and default data (For Inspection)
    loadPermitStatusCounts();
    loadPermitData('status', { status: 2 });
});
