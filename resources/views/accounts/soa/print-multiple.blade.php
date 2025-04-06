<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Multiple Statements of Account</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12pt;
        }
        
        .print-controls {
            text-align: center;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .statement-of-account {
            padding: 20px;
            margin-bottom: 30px;
            page-break-after: always;
        }
        
        .statement-of-account:last-child {
            page-break-after: avoid;
        }
        
        .logo {
            max-height: 80px;
        }
        
        .soa-header, .soa-footer {
            padding: 10px 0;
        }
        
        @media print {
            .no-print {
                display: none;
            }
            
            .statement-of-account {
                padding: 0;
                margin-bottom: 0;
                page-break-after: always;
            }
            
            .statement-of-account:last-child {
                page-break-after: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="print-controls no-print">
        <button class="btn btn-primary" onclick="window.print();">Print All Statements</button>
        <button class="btn btn-secondary" onclick="window.close();">Close</button>
    </div>
    
    @foreach($members as $member)
        <div class="statement-of-account">
            <div class="soa-header">
                <div class="row">
                    <div class="col-md-6">
                        <img src="{{ asset('images/logo.png') }}" alt="Logo" class="logo">
                        <h2>YOUR ASSOCIATION NAME</h2>
                        <p>Association Address Line 1</p>
                        <p>City, State ZIP</p>
                        <p>Phone: (123) 456-7890</p>
                        <p>Email: info@yourassociation.com</p>
                    </div>
                    <div class="col-md-6 text-right">
                        <h1>STATEMENT OF ACCOUNT</h1>
                        <p><strong>Statement Date:</strong> {{ date('F d, Y') }}</p>
                        <p><strong>Statement #:</strong> SOA-{{ date('Ymd') }}-{{ $member->mem_id }}</p>
                        <p><strong>For Period:</strong> {{ date('F Y', strtotime($member->arrear_month)) }} to {{ date('F Y', strtotime($member->current_month)) }}</p>
                    </div>
                </div>
            </div>
        
            <div class="soa-member-info mt-4">
                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0">MEMBER INFORMATION</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Member ID:</strong> {{ $member->mem_id }}</p>
                                <p><strong>Name:</strong> {{ $member->mem_name }}</p>
                                <p><strong>Member Type:</strong> {{ $member->mem_type }}</p>
                                <p><strong>SPA/Tenant:</strong> {{ $member->mem_SPA_Tenant ?: 'N/A' }}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Address ID:</strong> {{ $member->mem_add_id }}</p>
                                <p><strong>Monthly Dues:</strong> ₱{{ number_format($member->mem_monthlydues, 2) }}</p>
                                <p><strong>Arrears Count:</strong> {{ $member->current_arrear_count }} month(s)</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        
            <div class="soa-summary mt-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">ACCOUNT SUMMARY</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-bordered">
                                    <tr>
                                        <th>Previous Balance</th>
                                        <td class="text-right">₱{{ number_format($member->arrear, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <th>Current Monthly Dues</th>
                                        <td class="text-right">₱{{ number_format($member->mem_monthlydues, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <th>Interest Charges</th>
                                        <td class="text-right">₱{{ number_format($member->arrear_interest, 2) }}</td>
                                    </tr>
                                    <tr class="table-primary">
                                        <th>Total Amount Due</th>
                                        <th class="text-right">₱{{ number_format($member->arrear_total, 2) }}</th>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <div class="alert alert-info">
                                    <h5>Last Payment</h5>
                                    @if($member->last_paydate)
                                        <p><strong>Date:</strong> {{ date('F d, Y', strtotime($member->last_paydate)) }}</p>
                                        <p><strong>OR Number:</strong> {{ $member->last_or }}</p>
                                        <p><strong>Amount:</strong> ₱{{ number_format($member->last_payamount, 2) }}</p>
                                    @else
                                        <p>No recent payment recorded.</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        
            <div class="soa-details mt-4">
                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0">STATEMENT DETAILS</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered table-striped">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Description</th>
                                    <th class="text-right">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($member->arrear > 0)
                                    <tr>
                                        <td>Outstanding Balance ({{ $member->current_arrear_count }} month(s))</td>
                                        <td class="text-right">₱{{ number_format($member->arrear, 2) }}</td>
                                    </tr>
                                @endif
                                <tr>
                                    <td>Monthly Association Dues - {{ date('F Y', strtotime($member->current_month)) }}</td>
                                    <td class="text-right">₱{{ number_format($member->mem_monthlydues, 2) }}</td>
                                </tr>
                                @if($member->arrear_interest > 0)
                                    <tr>
                                        <td>Interest Charges (30% p.a. on outstanding balance)</td>
                                        <td class="text-right">₱{{ number_format($member->arrear_interest, 2) }}</td>
                                    </tr>
                                @endif
                                <tr class="font-weight-bold">
                                    <td>Total Amount Due</td>
                                    <td class="text-right">₱{{ number_format($member->arrear_total, 2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        
            <div class="soa-notes mt-4">
                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0">PAYMENT INSTRUCTIONS</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Due Date:</strong> Payment is due by the 5th of each month.</p>
                        <p><strong>Payment Methods:</strong></p>
                        <ul>
                            <li><strong>Bank Transfer:</strong> Bank Name, Account #12345678, Account Name: Association Name</li>
                            <li><strong>Checks:</strong> Payable to "Your Association Name"</li>
                            <li><strong>Cash:</strong> At the Association Office during business hours</li>
                        </ul>
                        <p><strong>Note:</strong> Please include your Member ID in all payment references.</p>
                        
                        <div class="alert alert-warning mt-3">
                            <p><strong>Important:</strong> Accounts with arrears exceeding 3 months are subject to interest charges 
                            and may result in the suspension of membership privileges according to the association by-laws.</p>
                        </div>
                    </div>
                </div>
            </div>
        
            <div class="soa-footer mt-4">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Prepared by:</strong> ____________________</p>
                        <p class="mt-4"><strong>Verified by:</strong> ____________________</p>
                    </div>
                    <div class="col-md-6 text-right">
                        <p>This statement is valid as of {{ date('F d, Y') }}</p>
                        <p class="mt-4">For questions about this statement, please contact our office at (123) 456-7890 or email accounting@yourassociation.com</p>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
    
    <script>
        window.onload = function() {
            document.title = "Multiple Statements of Account - {{ count($members) }} Members";
        };
    </script>
</body>
</html>