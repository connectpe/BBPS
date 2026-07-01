<style>
    .print-container {
        width: 300px;
        margin: auto;
        font-family: Arial;
        font-size: 12px;
        color: #000;
    }

    .print-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .print-logo {
        height: 30mm;
        width: 30mm;
    }

    .print-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }

    .print-table td {
        /* border-bottom: 1px solid #000; */
        padding: 5px 0;
    }

    .print-table td:first-child {
        width: 60%;
    }

    #printSection {
        display: none;
    }

    @media print {

        body * {
            visibility: hidden;
        }

        #printSection,
        #printSection * {
            visibility: visible;
        }

        #printSection {
            display: block !important;
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }

    }

    .receipt-header {
        width: 100%;
    }

    .left-logo {
        height: 30px;
    }

    .right-logo {
        height: 30mm;
        width: 30mm;
    }

    .success-text {
        color: green;
        font-weight: bold;
        margin-top: 5px;
    }


    @media print {

        .left-logo {
            height: 40px !important;
        }

        .right-logo {
            height: 50px !important;
        }

        .success-text {
            color: #000 !important;
        }

    }


    .print-only-logo {
        display: none;
    }

    @media print {

        .print-only-logo {
            display: block !important;
            height: 40px;
            margin-bottom: 7px;
        }

        @page {
            margin-top: 20mm;
        }
    }
</style>

<!-- Modal -->
<div class="modal fade" id="viewBillModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content p-3 rounded-4 shadow">

            <!-- Top Section -->
            <div class="text-center position-relative">

                <!-- Bharat Connect Logo -->
                <img src="{{ asset('assets/image/Logo/bharat-connect-logo.jpg') }}"
                    style="position:absolute; top:0; right:0; height:42px;">

                <h6 class="fw-semibold mt-2">Are you sure to proceed?</h6>

                <!-- Icon -->
                <div class="my-2">
                    <div class="rounded-circle border d-inline-flex align-items-center justify-content-center"
                        style="width:50px; height:50px;">
                        💰
                    </div>
                </div>

            </div>

            <!-- Consumer No -->
            <div class="d-flex justify-content-between px-2 mt-2">
                <span class="fw-semibold">Consumer No.</span>
                <span class="text-primary fw-semibold">9898990084</span>
            </div>

            <hr>

            <!-- Details -->
            <div class="px-2 small">

                <div class="d-flex justify-content-between py-1">
                    <span>Biller Name:</span>
                    <span class="fw-semibold">Sikkim Power-Urban</span>
                </div>

                <div class="d-flex justify-content-between py-1">
                    <span>Customer Name:</span>
                    <span>Rishi</span>
                </div>

                <div class="d-flex justify-content-between py-1">
                    <span>Customer Mob:</span>
                    <span>9898990045</span>
                </div>

                <div class="d-flex justify-content-between py-1">
                    <span>Bill Date:</span>
                    <span>2026-04-10</span>
                </div>

                <div class="d-flex justify-content-between py-1">
                    <span>Bill Period:</span>
                    <span>April</span>
                </div>

                <div class="d-flex justify-content-between py-1">
                    <span>Bill Number:</span>
                    <span>22303</span>
                </div>

                <div class="d-flex justify-content-between py-1">
                    <span>Due Date:</span>
                    <span>2026-04-18</span>
                </div>

                <div class="d-flex justify-content-between py-1">
                    <span>Bill Amount:</span>
                    <span>1100</span>
                </div>

                <div class="d-flex justify-content-between py-1">
                    <span>Customer Convenience Fees</span>
                    <span>0</span>
                </div>

                <div class="d-flex justify-content-between py-1">
                    <span>Payment Mode:</span>
                    <span>Cash</span>
                </div>

                <div class="d-flex justify-content-between py-1">
                    <span>Mobile Number</span>
                    <span>8739990084</span>
                </div>

            </div>

            <hr>

            <!-- Total -->
            <div class="d-flex justify-content-between px-2 fw-bold">
                <span>Total Amount</span>
                <span class="text-success">₹1100</span>
            </div>

            <!-- Charges Checkboxes -->
            <div class="px-2 mt-2 small">
                <label class="me-2">
                    <input type="checkbox"> Late Payment Fee(40)
                </label>
                <label class="me-2">
                    <input type="checkbox"> Fixed Charges(50)
                </label>
                <label>
                    <input type="checkbox"> Additional Charges(60)
                </label>
            </div>

            <!-- Buttons -->
            <div class="d-flex justify-content-between mt-4 px-2">
                <button class="btn buttonColor w-50 me-2 rounded-pill"
                    onclick="openSuccessModal('successModal','viewBillModal')">Pay</button>
                <button class="btn btn-outline-secondary w-50 rounded-pill" data-bs-dismiss="modal">
                    Cancel
                </button>
            </div>

        </div>
    </div>
</div>


<!-- Modal -->
<div class="modal fade" id="successModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content p-4 rounded-4 border-0 shadow">

            <!-- HEADER -->
            <div class="receipt-header">

                <!-- Top Row (Logos) -->
                <div class="d-flex justify-content-between align-items-center">

                    <!-- LEFT: Sidebar Logo -->
                    <img src="{{ asset('assets/image/Logo/sidebar-blue.png') }}" class="left-logo">

                    <!-- RIGHT: B-Assured Logo -->
                    <img src="{{ asset('assets/image/Logo/b-assured-logo.jpg') }}" class="right-logo">

                </div>

                <!-- Success Heading BELOW -->
                <h5 class="success-text">✔ Successful</h5>

            </div>

            <!-- Card Box -->
            <div class="p-4 rounded-4" style="background:#eaf4fb;">

                <div class="row small">

                    <!-- Left Column -->
                    <div class="col-md-6">

                        <div class="row mb-2">
                            <div class="col-6 text-muted">Bharat-Connect Txn ID</div>
                            <div class="col-6 fw-semibold">ABC85853058</div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-6 text-muted">Transaction Status</div>
                            <div class="col-6 fw-semibold text-success">Successful</div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-6 text-muted">Biller Name</div>
                            <div class="col-6">Sikkim Power-Urban</div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-6 text-muted">Bill Period</div>
                            <div class="col-6">April</div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-6 text-muted">Bill Number</div>
                            <div class="col-6">12303037</div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-6 text-muted">Due Date</div>
                            <div class="col-6">2026-04-18</div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-6 text-muted">Customer Name</div>
                            <div class="col-6">Rishi</div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-6 text-muted">Consumer A/C Number</div>
                            <div class="col-6">12303037</div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-6 text-muted">Bill Date</div>
                            <div class="col-6">2026-04-10</div>
                        </div>

                    </div>

                    <!-- Right Column -->
                    <div class="col-md-6">

                        <div class="row mb-2">
                            <div class="col-6 text-muted">Transaction Date</div>
                            <div class="col-6">10-Apr-2026 14:49</div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-6 text-muted">Payment Channel</div>
                            <div class="col-6">AGENT OUTLET</div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-6 text-muted">Biller ID</div>
                            <div class="col-6">OTME0005XXZ49</div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-6 text-muted">Payment Mode</div>
                            <div class="col-6">CASH</div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-6 text-muted">Amount</div>
                            <div class="col-6">1100</div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-6 text-muted">Customer Convenience Fee</div>
                            <div class="col-6">0</div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-6 text-muted">Total Amount</div>
                            <div class="col-6 fw-bold text-success">1100</div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-6 text-muted">Registered Mob No</div>
                            <div class="col-6">9898990045</div>
                        </div>

                    </div>

                </div>
            </div>

            <!-- Footer Buttons -->
            <div class="d-flex justify-content-center gap-3 mt-4">
                <button class="btn buttonColor px-5" onclick="sendEmail()">Email</button>
                <button class="btn buttonColor px-5" onclick="printReceipt()">Print</button>
            </div>

        </div>
    </div>
</div>


<!-- PRINTABLE RECEIPT -->
<div id="printSection" class="print-container">
    <!-- Print Only Logo -->
    <img src="{{ asset('assets/image/Logo/sidebar-blue.png') }}" class="print-only-logo">
    <!-- Header -->
    <div class="print-header">
        <h4>Receipt</h4>
        <img src="{{ asset('assets/image/Logo/b-assured-logo.jpg') }}" class="print-logo">
    </div>

    <!-- Table -->
    <table class="print-table">
        <tr>
            <td>Bharat-Connect Txn ID</td>
            <td>ABC85853058</td>
        </tr>
        <tr>
            <td>Transaction Status</td>
            <td>Successful</td>
        </tr>
        <tr>
            <td>Biller Name</td>
            <td>Sikkim Power-Urban</td>
        </tr>
        <tr>
            <td>Bill Period</td>
            <td>April</td>
        </tr>
        <tr>
            <td>Bill Number</td>
            <td>12303037</td>
        </tr>
        <tr>
            <td>Bill Due Date</td>
            <td>2026-04-18</td>
        </tr>
        <tr>
            <td>Bill Date</td>
            <td>2026-04-10</td>
        </tr>
        <tr>
            <td>Transaction Date</td>
            <td>10-Apr-2026 14:49</td>
        </tr>
        <tr>
            <td>Payment Channel</td>
            <td>AGENT OUTLET</td>
        </tr>
        <tr>
            <td>Payment Mode</td>
            <td>Cash</td>
        </tr>
        <tr>
            <td>Amount</td>
            <td>1100</td>
        </tr>
        <tr>
            <td>Customer Convenience Fee</td>
            <td>0</td>
        </tr>
        <tr>
            <td><strong>Total Amount</strong></td>
            <td><strong>1100</strong></td>
        </tr>
        <tr>
            <td>Mobile No</td>
            <td>9898990083</td>
        </tr>
    </table>

</div>


<script>
    function openSuccessModal(openModal, closeModal) {
        $("#" + closeModal).modal('hide');
        $("#" + openModal).modal('show');
    }

    function printReceipt() {
        window.print();
    }
</script>