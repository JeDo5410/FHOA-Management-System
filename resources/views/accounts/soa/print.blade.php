<!DOCTYPE html>
<html lang="en">
    @php
    function numberToWords($number) {
        $number = number_format($number, 2, '.', '');
        list($whole, $decimal) = explode('.', $number);
        
        $dictionary = [
            0 => 'zero',
            1 => 'one',
            2 => 'two',
            3 => 'three',
            4 => 'four',
            5 => 'five',
            6 => 'six',
            7 => 'seven',
            8 => 'eight',
            9 => 'nine',
            10 => 'ten',
            11 => 'eleven',
            12 => 'twelve',
            13 => 'thirteen',
            14 => 'fourteen',
            15 => 'fifteen',
            16 => 'sixteen',
            17 => 'seventeen',
            18 => 'eighteen',
            19 => 'nineteen',
            20 => 'twenty',
            30 => 'thirty',
            40 => 'forty',
            50 => 'fifty',
            60 => 'sixty',
            70 => 'seventy',
            80 => 'eighty',
            90 => 'ninety',
        ];
        
        $result = '';
        
        // Handle thousands
        if ($whole >= 1000) {
            $thousands = (int)($whole / 1000);
            $result .= (($thousands > 99) ? numberToWords($thousands) . ' ' : '') . 'thousand ';
            $whole %= 1000;
        }
        
        // Handle hundreds
        if ($whole >= 100) {
            $hundreds = (int)($whole / 100);
            $result .= $dictionary[$hundreds] . ' hundred ';
            $whole %= 100;
        }
        
        // Handle tens and units
        if ($whole <= 0) {
            $result = $result ?: 'zero';
        } elseif ($whole < 20) {
            $result .= $dictionary[$whole];
        } else {
            $tens = (int)($whole / 10) * 10;
            $units = $whole % 10;
            $result .= $dictionary[$tens];
            if ($units > 0) {
                $result .= '-' . $dictionary[$units];
            }
        }
        
        // Add cents if necessary
        if ((int)$decimal > 0) {
            $result .= ' and ' . $decimal . '/100';
        }
        
        return $result;
    }
    @endphp
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        @if($documentTypes === 'soa')
            Statement of Account
        @elseif($documentTypes === 'demand')
            Demand Letter
        @elseif($documentTypes === 'nncv1')
            Notice of Non-Compliance/Violation
        @else
            Document
        @endif
    </title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 16px;
            font-size: 11px;
        }
        
        .document-container {
            width: 100%;
            margin-top: 0;
            page-break-after: always;
        }
        
        .document-container:last-child {
            page-break-after: avoid;
        }
        
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }

        .header-table td {
            border: 1px solid black;
            padding: 3px; 
        }

        .logo-cell {
            width: 172px;
            height: 32px; 
            text-align: center;
        }

        .title-cell {
            text-align: center;
            font-weight: bold;
            font-size: 14px;
        }

        .form-info-label {
            width: 96px;
            font-weight: bold;
            font-size: 9px;
            padding: 1px; 
        }

        .form-info-value {
            width: 104px;
            font-size: 9px; 
            padding: 1px; 
        }
        
        .form-info-cell {
            width: 200px;
        }
        
        .form-info-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .form-info-table td {
            border: 1px solid black;
            padding: 3px 6px;
            font-size: 11px;
        }
        
        .form-info-table .label {
            width: 96px;
            font-weight: bold;
        }
        
        .customer-info {
            width: 100%;
            margin-top: 16px;
        }
        
        .customer-info-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }
        
        .customer-info-table td {
            padding: 3px 0;
            vertical-align: top;
        }
        
        .value-field {
            border-bottom: 1px solid black;
        }
        
        .fixed-width-field {
            width: 240px;
            border-bottom: 1px solid black;
            display: inline-block;
        }
        
        .label {
            font-weight: bold;
            width: 80px;
        }

        .letter-info {
            font-size: 11px;
        }
        
        .right-align {
            text-align: right;
        }
        
        .interest-note {
            color: red;
            font-style: italic;
            text-align: right;
            margin-top: 4px;
            font-size: 11px;
        }
        
        .reference-number {
            text-align: right;
            font-weight: bold;
            color: #844121;
            margin-bottom: 10px;
        }
        
        .financial-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
            margin-bottom: 12px;
        }
        
        .financial-table th, .financial-table td {
            border: 1px solid black;
            padding: 5px;
        }
        
        .note {
            font-size: 11px;
            margin-top: 8px;
        }
        
        .demand-text {
            text-align: justify;
            margin: 12px 0;
            line-height: 1.4;
        }
        
        .signature-block {
            margin-top: 20px;
        }
        
        .signature-line {
            border-top: 1px solid black;
            width: 200px;
            margin-top: 20px;
        }
        
        .document-divider {
            border-top: 2px dashed #444;
            margin: 40px 0;
            position: relative;
        }
        
        .divider-text {
            position: absolute;
            top: -10px;
            left: 50%;
            transform: translateX(-50%);
            background-color: white;
            padding: 0 10px;
            font-weight: bold;
            font-style: italic;
            font-size: 11px;
            color: #444;
        }
        
        /* Print styles */
        @media print {
            .no-print {
                display: none;
            }
            
            .document-container {
                padding: 0;
                padding-top: 10mm !important; 
                page-break-after: always;
                margin: 0;
            }
            
            .document-container:last-child {
                page-break-after: avoid;
            }
            
            @page {
                size: auto;
                margin: 10mm;
                margin-top: 0mm;    /* Remove space for header */
                margin-bottom: 0mm; /* Remove space for footer */
            }
            
            /* Explicitly hide headers and footers */
            @page :first {
                margin-top: 0mm;
                margin-bottom: 0mm;
            }
            
            @page :left {
                margin-top: 0mm;
                margin-bottom: 0mm;
            }
            
            @page :right {
                margin-top: 0mm;
                margin-bottom: 0mm;
            }
            
        }
        
        /* Button styles */
        .btn {
            padding: 5px 10px;
            margin-right: 4px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-size: 9px;
        }
        
        .btn-primary {
            background-color: #007bff;
            color: white;
        }
        
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        
        .print-controls {
            margin-bottom: 16px;
        }
    </style>
</head>
@php 
    // Ensure we have an array of document types
    if (!isset($documentTypes)) {
        $documentTypes = ['soa'];
    } elseif (!is_array($documentTypes)) {
        $documentTypes = [$documentTypes];
    }
@endphp
<body>
    <div class="print-controls no-print">
        <button class="btn btn-primary" onclick="window.print();">Print Documents</button>
        <button class="btn btn-secondary" onclick="window.close();">Close</button>
    </div>
    @foreach($documentTypes as $documentType)
        @if($documentType === 'soa')

        <!-- ====================================================================== -->
        <!-- STATEMENT OF ACCOUNT (SOA) - HOMEOWNERS/TENANTS BILLING DOCUMENT       -->
        <!-- ====================================================================== -->

        <div class="document-container" style="margin-top: 0; padding-top: 0;">
            <!-- Header Table with Logo, Title and Form Info -->

            <table class="header-table">
                <tr>
                    <td class="logo-cell" rowspan="4">
                        <img class="logo-img" src="{{ asset('assets/images/Picture1.jpg') }}" alt="Logo">
                    </td>
                    <td class="title-cell" rowspan="4">
                        <div>STATEMENT OF ACCOUNT</div>
                        <div>(HOMEOWNERs / TENANTs)</div>
                    </td>
                    <td class="form-info-label">FORM NO.</td>
                    <td class="form-info-value">AD - 015 A</td>
                </tr>
                <tr>
                    <td class="form-info-label">REVISION NO.</td>
                    <td class="form-info-value">04</td>
                </tr>
                <tr>
                    <td class="form-info-label">EFFECTIVITY</td>
                    <td class="form-info-value">1/29/2025</td>
                </tr>
                <tr>
                    <td class="form-info-label">PAGE NO.</td>
                    <td class="form-info-value">1 OF 1</td>
                </tr>
            </table>
            
            <!-- Customer Information -->
            <div class="customer-info">
                <table class="customer-info-table">
                    <tr>
                        <td class="label">SOA NO. :</td>
                        <td><span class="fixed-width-field"></span></td>
                        <td class="right-align"><strong>BILLING DATE:</strong></td>
                        <td width="160" style="text-align: right;" class="value-field">{{ date('M.j, Y') }}</td>
                    </tr>
                    <tr>
                        <td class="label">NAME :</td>
                        <td><span class="fixed-width-field"><strong>MR./MS.</strong> {{ $member->mem_name }}</span></td>
                        <td class="right-align"><strong>BILL MONTH :</strong></td>
                        <td style="text-align: right;" class="value-field">
                            {{ date('F Y') }}
                        </td>
                    </tr>
                    <tr>
                        <td class="label">ADDRESS:</td>
                        <td><span class="fixed-width-field">
                            @php
                            $addressId = $member->mem_add_id;
                            if (strlen($addressId) === 5) {
                                $phase = substr($addressId, 0, 1);
                                $block = substr($addressId, 1, 2);
                                $lot = substr($addressId, 3, 2);
                                echo "Ph. {$phase} Blk. {$block} Lot {$lot}";
                            } else {
                                echo $addressId;
                            }
                            @endphp
                        </span></td>
                        <td class="right-align"><strong>DUE DATE :</strong></td>
                        <td style="text-align: right;" class="value-field">ASAP</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td><span class="fixed-width-field">FORTEZZA SUBDIVISION</span></td>
                        <td colspan="2" class="interest-note">*30% per annum Interest will apply after the due date</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td><span class="fixed-width-field">SAN ISIDRO, CITY of CABUYAO 4025 LAGUNA</span></td>
                        <td colspan="2"></td>
                    </tr>
                </table>
            </div>
            
            <!-- Financial Details Table -->
            <div class="financial-details" style="margin-top: 16px;">
                <table style="width: 100%; border-collapse: collapse; margin-top: 12px;">
                    <tr>
                        <td style="border: 1px solid black; padding: 5px;">PREVIOUS MONTH UNPAID MAINTENANCE DUES</td>
                        <td style="border: 1px solid black; padding: 5px; text-align: right; width: 120px;">{{ number_format($member->arrear - $member->mem_monthlydues, 2) }}</td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid black; padding: 4.5px;">CURRENT MONTH MAINTENANCE DUES</td>
                        <td style="border: 1px solid black; padding: 5px; text-align: right;">{{ number_format($member->mem_monthlydues, 2) }}</td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid black; padding: 4.5px;">TOTAL AMOUNT OF UNPAID MONTHLY MAINTENANCE DUES</td>
                        <td style="border: 1px solid black; padding: 5px; text-align: right;">{{ number_format($member->arrear, 2) }}</td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid black; padding: 5px; font-style: italic;">INTEREST (@30% per Annum; based on FHOAI By-Laws in Page 4, Article V, Section 2.b )</td>
                        <td style="border: 1px solid black; padding: 5px; text-align: right;">{{ number_format($member->arrear_interest, 2) }}</td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid black; padding: 5px; font-weight: bold; font-style: italic;">GRAND TOTAL AMOUNT OF PAYABLES (As of Date of creation of SOA)</td>
                        <td style="border: 1px solid black; padding: 5px; text-align: right; font-weight: bold;">{{ number_format($member->arrear_total, 2) }}</td>
                    </tr>
                </table>
            </div>
            
            <!-- Commitments and Reminders Section -->
            <div style="margin-top: 16px; font-size: 11px;">
                <p style="margin-bottom: 6px;"><strong>* Per commitment to pay UNPAID MONTHLY MAINTENANCE DUES as per Promissory Note arranged with the FHOAI - Board of Directors (BOD)...</strong></p>
                <p style="margin-bottom: 6px;"><strong>** REMINDERs:</strong></p>
                
                <div style="margin-left: 16px; margin-right: 16px; text-align: justify;">
                    <p style="margin-bottom: 4px;">Non-payment of arrears shall have the Board of Directors (BOD) declare you as DELINQUENT MEMBER or MEMBER NOT IN GOOD STANDING as provided in the Department of Human Settlements & Urban Development (DHSUD)'s Revised Implementing Rules & Regulations (RIRR) of RA 9904 - Magna Carta for Homeowners & Homeowners Associations' Rule III-Membership in the Association, Pages 10-11:</p>
                    
                    <p style="margin-bottom: 4px;"><strong>- Section 16. Deliquent Member or Member Not in Good Standing.</strong></p>
                    <p style="margin-bottom: 4px; margin-left: 8px;">Unless otherwise provided in the ByLaws, a Member may be declared DELINQUENT or NOT IN GOOD STANDING by the Board of Directors on any of the following grounds:</p>
                    <p style="margin-bottom: 4px; margin-left: 15px;">a. Failure to pay at least three (3) cumulative monthly dues or membership fees, and/ or other charges and/ or assessments despite repeated demands by the association;</p>
                    <p style="margin-bottom: 4px; margin-left: 8px;">A member who has been declared delinquent or not in good standing in accordance with the procedure in the succeeding Section is not entitled to exercise the rights of a member, but is nevertheless obliged to pay all fees and dues assessed a member in good standing.</p>
                    
                    <p style="margin-bottom: 4px;"><strong>- Section 18. Sanctions against a Delinquent Member or a Member Not in Good Standing.</strong></p>
                    <p style="margin-bottom: 4px; margin-left: 8px;">The rights and privileges of a member is suspended upon the declaration of delinquency by the Board. Such suspension shall extend to his/her co-owner extend to his/ her co-owner/ homeowners or co-lessees, as applicable, and all those residing in his/ her property.</p>
                    
                    <p style="margin-bottom: 4px;"><strong>***FHOAI's By-Laws, Page 4 - Article V, Section 2.b:</strong> "…The Maintenance Dues will be payabe in full every five (5) calendar days of each month, if not paid within said period, the member shall be considered as delinquent and the amount due shall be charged with an interest in the amount of thirty (30%) percent per annum until fully paid; if not paid within thirty (30) calendar days from due date, the Association shall have the right to take necessary legal acti on against the delinquent members.</p>
                </div>
            </div>
            
            <!-- Signature Section -->
            <div style="display: flex; justify-content: space-between;">
                <div style="width: 45%;  margin-top: 0;">
                    <p>Prepared by:</p>
                    <div style="margin-top: 20px; border-top: 1px solid black; margin-right: 16px;">
                        <p style="text-align: center; margin-bottom: 0;">
                            <!--    CHARLES B. ICAMINA  -->
                        </p>
                        <p style="text-align: center; margin-top: 0;">Admin Assistant</p>
                    </div>
                </div>
                <div style="width: 45%; text-align: left; margin-top: 0;">
                    <p>Noted by:</p>
                    <div style="margin-top: 20px; border-top: 1px solid black; margin-left: 16px;">
                        <p style="text-align: center; margin-bottom: 0;">GEORGINA M. SCHRIER / ARIEL M. AREGLO</p>
                        <p style="text-align: center; margin-top: 0;">FHOAI - BOD Treasurer / President</p>
                    </div>
                </div>
            </div>
            
            <!-- Payment Options and Comments Table -->
            <div style="margin-top: 10px;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="border: 1px solid black; padding: 8px; width: 70%; vertical-align: top;">
                            <p style="margin-top: 0; margin-bottom: 6px; font-weight: bold;">PAYMENT OPTIONS:</p>
                            <p style="margin-top: 0; margin-bottom: 4px;"><strong>1) ADMIN OFFICE c/o Admin Asst. at 8AM - 5PM on Monday - Saturday</strong></p>
                            <p style="margin: 0 0 0 16px;">Contact Details: <span style="color: blue;">Globe CP# 0917 105 7841</span></p>
                            <p style="margin: 0 0 6px 16px;">Email Address: <span style="color: blue;">fortezzahomeowners2020@gmail.com</span></p>
                            
                            <p style="margin-top: 0; margin-bottom: 4px;"><strong>2) Bank Deposit Over the Counter or via Online Banking:</strong></p>
                            <p style="margin: 0 0 0 16px;">PNB Account Name: <span style="color: blue;">FORTEZZA HOMEOWNERS' ASSOCIATION, INC.</span></p>
                            <p style="margin: 0 0 6px 16px;">PNB Checking Account No. <span style="color: blue;">2463 - 7000 - 2626</span></p>
                            
                            <p style="margin-top: 0; margin-bottom: 4px;"><strong>3) For check payments,</strong></p>
                            <p style="margin: 0 0 6px 16px;">check must be payabe to <span style="color: blue;">FORTEZZA HOMEOWNERS' ASSOCIATION, INC.</span></p>
                            
                            <p style="margin-top: 0; margin-bottom: 4px;"><strong>4) GCASH Payment :</strong></p>
                        </td>
                        <td style="border: 1px solid black; padding: 8px; width: 30%; vertical-align: top;">
                            <p style="margin-top: 0; margin-bottom: 6px; font-weight: bold;">COMMENT/s:</p>
                            <div style="height: 120px;"></div>
                        </td>
                    </tr>
                </table>
            </div>
            
            <!-- Received By Section -->
            <div style="margin-top: 16px;">
                <table style="width: 100%;">
                    <tr>
                        <td style="width: 50%; vertical-align: top;">
                            <p>Received by:</p>
                            <div style="width: 90%; border-bottom: 1px solid black; margin-top: 24px;"></div>
                            <p style="text-align: center; font-size: 8px; margin-top: 4px;">(Signature Over Printed Name)</p>
                        </td>
                        <td style="width: 50%; vertical-align: top;">
                            <table style="width: 100%;">
                                <tr>
                                    <td style="width: 64px; padding-top: 12px;">Date:</td>
                                    <td style="border-bottom: 1px solid black;"></td>
                                </tr>
                                <tr>
                                    <td style="width: 64px; padding-top: 12px;">Time:</td>
                                    <td style="border-bottom: 1px solid black;"></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
                
                <p style="margin-top: 16px;">CC: 201 File</p>
            </div>
        </div>
        @endif
        <!-- No divider between documents -->

        @if($documentType === 'nncv1')

        <!-- ====================================================================== -->
        <!-- NOTICE OF NON-COMPLIANCE/VIOLATION (NNCV1) - FIRST WARNING NOTICE      -->
        <!-- ====================================================================== -->

        <div class="document-container" style="margin-top: 25px; padding-top: 25px;">
            <!-- Header Table with Logo, Title and Form Info -->
            <table class="header-table">
                <tr>
                    <td class="logo-cell" rowspan="4">
                        <img class="logo-img" src="{{ asset('assets/images/Picture1.jpg') }}" alt="Logo">
                    </td>
                    <td class="title-cell" rowspan="4">
                        NOTICE OF NON-COMPLIANCE /<br>VIOLATION
                    </td>
                    <td class="form-info-label">FORM NO.</td>
                    <td class="form-info-value">AD - 019 A</td>
                </tr>
                <tr>
                    <td class="form-info-label">REVISION NO.</td>
                    <td class="form-info-value">3</td>
                </tr>
                <tr>
                    <td class="form-info-label">EFFECTIVITY</td>
                    <td class="form-info-value">05/10/2023</td>
                </tr>
                <tr>
                    <td class="form-info-label">PAGE NO.</td>
                    <td class="form-info-value">1 OF 1</td>
                </tr>
            </table>
            
            <!-- Reference Number -->
            <div class="reference-number">
                NNCV1 - {{ date('Y') }}/{{ sprintf('%03d', $member->mem_id) }}
            </div>
            
            <!-- Letter Information -->
            <div class="letter-info">
                <table>
                    <tr>
                        <td width="60"><strong>DATE :</strong></td>
                        <td class="value-field">{{ date('M.j, Y') }}</td>
                    </tr>
                    <tr>
                        <td><strong>TO :</strong></td>
                        <td><span class="fixed-width-field"><strong>MR./MS.</strong> {{ $member->mem_name }}</span></td>
                    </tr>
                    <tr>
                        <td><strong>ADDRESS:</strong></td>
                        <td><span class="fixed-width-field">
                            @php
                            $addressId = $member->mem_add_id;
                            if (strlen($addressId) === 5) {
                                $phase = substr($addressId, 0, 1);
                                $block = substr($addressId, 1, 2);
                                $lot = substr($addressId, 3, 2);
                                echo "Ph. {$phase} Blk. {$block} Lot {$lot}";
                            } else {
                                echo $addressId;
                            }
                            @endphp
                        </span></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td><span class="fixed-width-field">FORTEZZA SUBDIVISION</span></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td><span class="fixed-width-field">SAN ISIDRO, CITY of CABUYAO 4025 LAGUNA</span></td>
                    </tr>
                </table>
            </div>
            
            <!-- Salutation and Introduction -->
            <div style="margin-bottom: 16px;">
                <p><strong>Dear Mr./Ms. {{ $member->mem_name }}</strong></p>
                <p>It has come to our attention that you are one of the Homeowners in 
                    @php
                    $addressId = $member->mem_add_id;
                    if (strlen($addressId) === 5) {
                        $phase = substr($addressId, 0, 1);
                        $block = substr($addressId, 1, 2);
                        $lot = substr($addressId, 3, 2);
                        echo "Ph. {$phase} Blk. {$block} Lot {$lot}";
                    } else {
                        echo $addressId;
                    }
                    @endphp
                who have not yet paid your monthly maintenance dues amounting to  ({{ strtoupper(numberToWords($member->arrear)) }} PESOS) ₱{{ number_format($member->arrear, 2) }}. 
                Thus, your payables as of {{ date('M.j, Y') }} amount to total of ({{ strtoupper(numberToWords($member->arrear_total)) }} PESOS) ₱{{ number_format($member->arrear_total, 2) }}. 
                Including the 30% per annum penalty charges for your {{ $member->current_arrear_count }} months of arrears as per attached Statement of Account (SOA) in Annex A.</p>
            </div>
            
            <!-- Main Content -->
            <div class="demand-text">
                <p>As per <em>DHSUD's Department Order No. 2021-007, Series of 2021 – 2021 Revised Implementing Rules and Regulations (IRR) of Republic Act No. 9904</em>, otherwise known as <em>the Magna Carta for Homeowners & Homeowners Associations</em>, this letter serves as our Written Notice on your violation of its <em>Sections 13-16 in Pages 9-11</em>, and non-compliance with our <em>FHOAI's Rules & Regulations' Section XV B.1-B.4</em> concerning delinquency in paying monthly maintenance dues <em>(details in attached SOA No. H&T / {{ date('Y') }} – {{ sprintf('%03d', $member->mem_id) }})</em>.</p>
                
                <p>You are hereby given sixty (60) days grace period upon receipt of this notice, to fully settle your unpaid monthly maintenance dues. Please respond in writing within fifteen (15) days upon receipt of this notice for setting an appointment with the BODs on how you plan to settle your payables, if you shall avail of such grace period, and why you should not be declared as Delinquent Member of the Association.</p>
                
                <p>Please be reminded that the 30% per annum interest for penalty / charges of unpaid monthly maintenance dues shall continuously apply while you have not fully paid for them, as per FHOAI {{ date('Y') }} / CIR-003.</p>
                
                <p>Not responding to this notice and/or not taking action to address non-payment of dues as per given deadline, shall automatically waive your rights as a Member of Good Standing of the FHOAI, which can lead to SANCTIONS as mandated in FHOAI {{ date('Y') }} / CIR – 003 (in alignment with the <em>2021 IRR of RA 9904 – Rule III, Section 18, Page 11</em>). Moreover, collection shall be legally facilitated through Small Money Claims.</p>
                
                <p>For your immediate attention and prompt action, thank you.</p>
            </div>
            
            <!-- Signature Section -->
            <div style="margin-top: 20px;">
                <p>THE FHOAI BOARD OF DIRECTORS:</p>
                
                <div style="display: flex; justify-content: space-between; margin-top: 15px;">
                    <div style="width: 45%;">
                        <div class="signature-line"></div>
                        <p style="margin-top: 5px; margin-bottom: 5px;"><strong>GEORGINA M. SCHRIER</strong><br>
                        FHOAI-BOD Treasurer</p>
                    </div>
                    <div style="width: 45%;">
                        <div class="signature-line" style="margin-left: auto;"></div>
                        <p style="margin-top: 5px; margin-bottom: 5px; text-align: right;"><strong>ARIEL M. AREGLO</strong><br>
                        FHOAI – BOD President</p>
                    </div>
                </div>
            </div>
            
            <!-- Acknowledgment Section -->
            <div style="margin-top: 40px; padding-top: 10px;">
                <p>CC: 201 File</p>
            </div>
        </div>
        <!-- No divider between documents -->
        @endif

        @if($documentType === 'demand')

        <!-- ====================================================================== -->
        <!-- DEMAND LETTER - FINAL NOTICE FOR DELINQUENT MEMBERS                    -->
        <!-- ====================================================================== -->

        <div class="document-container" style="margin-top: 25px; padding-top: 25px;">
            <!-- Header Table with Logo, Title and Form Info -->
            <table class="header-table">
                <tr>
                    <td class="logo-cell" rowspan="4">
                        <img class="logo-img" src="{{ asset('assets/images/Picture1.jpg') }}" alt="Logo">
                    </td>
                    <td class="title-cell" rowspan="4">
                        DEMAND LETTER
                    </td>
                    <td class="form-info-label">FORM NO.</td>
                    <td class="form-info-value">AD - 021</td>
                </tr>
                <tr>
                    <td class="form-info-label">REVISION NO.</td>
                    <td class="form-info-value">0</td>
                </tr>
                <tr>
                    <td class="form-info-label">EFFECTIVITY</td>
                    <td class="form-info-value">09/21/2024</td>
                </tr>
                <tr>
                    <td class="form-info-label">PAGE NO.</td>
                    <td class="form-info-value">1 OF 1</td>
                </tr>
            </table>
            
            <!-- Reference Number -->
            <div class="reference-number">
                DL - {{ date('Y') }}/{{ sprintf('%03d', $member->mem_id) }}
            </div>
            
            <!-- Letter Information -->
            <div class="letter-info">
                <table>
                    <tr>
                        <td width="60"><strong>DATE :</strong></td>
                        <td class="value-field">{{ date('M.j, Y') }}</td>
                    </tr>
                    <tr>
                        <td><strong>TO :</strong></td>
                        <td><span class="fixed-width-field"><strong>MR./MS.</strong> {{ $member->mem_name }}</span></td>
                    </tr>
                    <tr>
                        <td><strong>ADDRESS:</strong></td>
                        <td><span class="fixed-width-field">
                            @php
                            $addressId = $member->mem_add_id;
                            if (strlen($addressId) === 5) {
                                $phase = substr($addressId, 0, 1);
                                $block = substr($addressId, 1, 2);
                                $lot = substr($addressId, 3, 2);
                                echo "Ph. {$phase} Blk. {$block} Lot {$lot}";
                            } else {
                                echo $addressId;
                            }
                            @endphp
                        </span></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td><span class="fixed-width-field">FORTEZZA SUBDIVISION</span></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td><span class="fixed-width-field">SAN ISIDRO, CITY of CABUYAO 4025 LAGUNA</span></td>
                    </tr>
                </table>
            </div>
            
            <!-- Salutation and Introduction -->
            <div style="margin-bottom: 16px;">
                <p><strong>Dear Mr./Ms. {{ $member->mem_name }}</strong></p>
                <p>It has come to our attention that you are one of the Homeowners/Tenants who have not yet paid your monthly maintenance dues of the below details:</p>
            </div>
            
            <!-- Financial Details Table -->
            <table class="financial-table">
                <tr>
                    <td style="border: 1px solid black; padding: 5px;"><strong># UNPAID MONTHS</strong></td>
                    <td style="border: 1px solid black; padding: 5px; text-align: right;">
                        {{ $member->current_arrear_count }} month(s)
                    </td>
                </tr>
                <tr>
                    <td style="border: 1px solid black; padding: 5px;"><strong>PREVIOUS MONTH UNPAID MAINTENANCE DUES</strong></td>
                    <td style="border: 1px solid black; padding: 5px; text-align: right;">
                        {{ number_format($member->arrear - $member->mem_monthlydues, 2) }}
                    </td>
                </tr>
                <tr>
                    <td style="border: 1px solid black; padding: 5px;"><strong>CURRENT MONTH MAINTENANCE DUES</strong></td>
                    <td style="border: 1px solid black; padding: 5px; text-align: right;">
                        {{ number_format($member->mem_monthlydues, 2) }}
                    </td>
                </tr>
                <tr>
                    <td style="border: 1px solid black; padding: 5px;"><strong>TOTAL AMOUNT OF UNPAID MONTHLY MAINTENANCE DUES</strong></td>
                    <td style="border: 1px solid black; padding: 5px; text-align: right;">
                        {{ number_format($member->arrear, 2) }}
                    </td>
                </tr>
                <tr>
                    <td style="border: 1px solid black; padding: 5px;"><strong>INTEREST (@30% per Annum; based on FHOAI By-Laws in Page 4, Article V, Section 2.b)</strong></td>
                    <td style="border: 1px solid black; padding: 5px; text-align: right;">
                        {{ number_format($member->arrear_interest, 2) }}
                    </td>
                </tr>
                <tr>
                    <td style="border: 1px solid black; padding: 5px;"><strong>GRAND TOTAL AMOUNT OF PAYABLES (As of Date of creation of SOA)</strong></td>
                    <td style="border: 1px solid black; padding: 5px; text-align: right; font-weight: bold;">
                        {{ number_format($member->arrear_total, 2) }}
                    </td>
                </tr>
            </table>
            
            <!-- Note -->
            <div class="note">
                <strong>NOTE:</strong> Including the 30% per annum penalty charges of arrears as per attached Statement of Account (SOA) in Annex A.
            </div>
            
            <!-- Demand Text -->
            <div class="demand-text">
                <p>Several months had passed since your receipt of the 1<sup>st</sup> Notice of Non-compliance & Violation (NNCV1) to pay your arrears and avail of the sixty (60) days period.</p>
                
                <p>As per <em>DHSUD's 2021 Revised Implementing Rules and Regulations (IRR) of Republic Act No. 9904</em>, otherwise known as <em>the Magna Carta for Homeowners & Homeowners Associations</em>, this serves as a REMINDER of your violation of its <em>Sections 9-16 in Pages 9-11</em>, and non-compliance with our <em>FHOAI's Rules & Regulations' Section XV B.1-B.4</em> concerning delinquency in paying monthly maintenance dues <em>(details in attached SOA No. H&T / 2025 – 027)</em>.</p>
                
                <p>Also attached is a copy of the FHOAI – Board of Directors (BODs)' Board Resolution dated 31 October 2024 (<em>Annex B</em>), declaring you as FHOAI's <strong>DELINQUENT MEMBER or a MEMBER NOT IN GOOD STANDING.</strong> You are given ten (10) days to file a motion for reconsideration with the BODs.</p>
                
                <p>Please be reminded that the interest of 30% per annum for penalty / charges of unpaid monthly maintenance dues shall continuously apply while you have not fully paid for them, as per FHOAI 2025 / CIR-004.</p>
                
                <p>Such declaration of delinquency by the BODs suspend your rights and privileges as a member with SANCTIONS as mandated in FHOAI 2025 / CIR – 004 (in alignment with the <em>2021 IRR of RA 9904 – Rule III, Section 18, Page 11</em>). Collection shall be legally facilitated through Small Money Claims.</p>
                
                <p>We look forward to your most favorable action on this matter, as well as to your cooperation and conscientiousness in being a part of the solution to sustain the FHOAI's monthly operational expenses.</p>
            </div>
            
            <!-- Signature Section -->
            <div class="signature-block">
                <p>THE FHOAI BOARD OF DIRECTORS:</p>
                
                <div style="display: flex; justify-content: space-between; margin-top: 15px;">
                    <div style="width: 45%;">
                        <div class="signature-line"></div>
                        <p style="margin-top: 5px; margin-bottom: 5px;"><strong>GEORGINA M. SCHRIER</strong><br>
                        FHOAI-BOD Treasurer</p>
                    </div>
                    <div style="width: 45%;">
                        <div class="signature-line" style="margin-left: auto;"></div>
                        <p style="margin-top: 5px; margin-bottom: 5px; text-align: right;"><strong>BALTAZAR P. DEJORAS</strong><br>
                        FHOAI – BOD Auditor</p>
                    </div>
                </div>
                
                <div style="display: flex; justify-content: space-between; margin-top: 15px;">
                    <div style="width: 45%;">
                        <div class="signature-line"></div>
                        <p style="margin-top: 5px; margin-bottom: 5px;"><strong>JOHN PAUL A. LAGUILLES</strong><br>
                        FHOAI – BOD Secretary</p>
                    </div>
                    <div style="width: 45%;">
                        <div class="signature-line" style="margin-left: auto;"></div>
                        <p style="margin-top: 5px; margin-bottom: 5px; text-align: right;"><strong>JESSAMINE D. LAYGO</strong><br>
                        FHOAI – BOD Vice President</p>
                    </div>
                </div>
                
                <div style="margin-top: 15px; text-align: center;">
                    <div class="signature-line" style="margin: 0 auto;"></div>
                    <p style="margin-top: 5px; margin-bottom: 5px;"><strong>ARIEL M. AREGLO</strong><br>
                    FHOAI – BOD President</p>
                </div>
            </div>
            
            <!-- Acknowledgment Section -->
            <div style="margin-top: 40px; padding-top: 10px;">
                <p>CC: 201 File</p>
            </div>
        </div>
        @endif
    @endforeach

    <script>
        // Script to suppress headers and footers in print
        window.onbeforeprint = function() {
            // Set page title to empty to prevent it from appearing in print header
            var originalTitle = document.title;
            document.title = "";
            
            // Add a meta tag to help suppress headers/footers
            var meta = document.createElement('meta');
            meta.name = 'viewport';
            meta.content = 'width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no';
            document.getElementsByTagName('head')[0].appendChild(meta);
            
            // Restore title after printing
            setTimeout(function() {
                document.title = originalTitle;
                document.getElementsByTagName('head')[0].removeChild(meta);
            }, 100);
        };
    </script>
</body>
</html>