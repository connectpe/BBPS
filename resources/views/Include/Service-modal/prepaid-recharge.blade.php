<!-- Recharge Modal -->
<div class="modal fade" id="rechargeModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header position-relative overflow-visible">
                <h5 class="modal-title" id="modalTitle">Mobile Prepaid Recharge</h5>

                <img src="{{ asset('assets/image/Logo/bharat-connect-logo.jpg') }}" class="position-absolute"
                    style="top: 10px; right: 50px; width: 70px;">

                <button type="button" class="btn-close position-absolute bg-light" data-bs-dismiss="modal"
                    style="right: 18px;">
                </button>
            </div>

            <div class="modal-body" id="modalBody"></div>

            <div class="modal-footer" id="modalFooter"></div>

        </div>
    </div>
</div>

<script>
    (function () {

        let currentStep = 1;
        let isFetching = false;

        window.selectedMeta = {};
        window.cachedPlans = [];

        // ======================
        // CSRF FIX
        // ======================
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // ======================
        // INIT
        // ======================
        $('#rechargeModal').on('shown.bs.modal', function () {
            renderStep(1);
        });

        $('#rechargeModal').on('hidden.bs.modal', function () {
            selectedMeta = {};
            cachedPlans = [];
        });

        // ======================
        // STEP CONTROLLER
        // ======================
        function renderStep(step) {
            currentStep = step;
            if (step === 1) renderForm();
            if (step === 2) renderPlans(); // ✅ FIXED (no param needed)
            if (step === 3) renderPayment();
        }

        // ======================
        // STEP 1: FORM
        // ======================
        function renderForm() {

            $('#modalTitle').text('Mobile Prepaid Recharge');

            $('#modalBody').html(`
            <div class="mb-3">
                <label class="form-label">Mobile Number</label>
                <input class="form-control" id="mobile" placeholder= "Enter Mobile No.">
            </div>

            <div class="mb-3">
                <label class="form-label">Operator</label>
                <select class="form-select" id="operator">
                    <option value="">Select</option>
                    @foreach ($rechargeOperators as $op)
                        <option value="{{ $op['op_id'] }}">{{ $op['name'] }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Circle</label>
                <select class="form-select" id="circle">
                    <option value="">Select</option>
                    @foreach ($rechargeCircles as $circle)
                        <option value="{{ $circle['circle_id'] }}">{{ $circle['name'] }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Plan Type</label>
                <select class="form-select" id="planType">
                    <option value="">Select</option>
                    @foreach ($rechargePlanTypes as $plan)
                        <option value="{{ $plan['plan_id'] }}">{{ $plan['name'] }}</option>
                    @endforeach
                </select>
            </div>
        `);

            $('#modalFooter').html(`
            <button class="btn buttonColor" id="viewPlansBtn">View Plans</button>
        `);
        }

        // ======================
        // STEP 2: PLANS
        // ======================
        function renderPlans() {

            let plans = cachedPlans || []; // ✅ FIX

            let html = `
            ${renderMetaHeader(selectedMeta)}
            <div class="plans-wrap"><div class="plans-scroll">
        `;

            plans.forEach((p) => {

                const amt = p.amount ?? p.price ?? p.recharge_amount ?? p.amt ?? 0;

                let validity = p.validity ?? p.validityDays ?? p.days ?? p.planValidity ?? '';
                let desc = p.description ?? p.desc ?? p.planName ?? '';

                let talktime =
                    p.talktime ??
                    p.talkTime ??
                    p.talktime_amount ??
                    p.talktimeAmount ??
                    p.talk_value ?? '';

                validity = (validity + '').trim() || '—';
                desc = (desc + '').trim() || 'Plan details';

                if (talktime) desc += ` • Talktime ₹${talktime}`;

                html += `
                <button type="button" class="plan-card plan" data-amt="${amt}">
                    <div class="plan-left">
                        <div class="plan-badge">₹</div>
                        <div class="plan-meta">
                            <p class="plan-amt">₹${amt}</p>
                            <p class="plan-sub">${desc}</p>
                        </div>
                    </div>

                    <div class="plan-right">
                        <span class="plan-chip">${validity}</span>
                        <i class="bi bi-chevron-right"></i>
                    </div>
                </button>
            `;
            });

            html += `</div></div>`;

            $('#modalBody').html(html);

            $('#modalFooter').html(`
            <button class="btn btn-secondary" id="backToForm">Back</button>
        `);

            // Select Plan
            $('.plan').off('click').on('click', function () {
                selectedMeta.amount = $(this).data('amt');
                renderStep(3);
            });
        }

        // ======================
        // STEP 3: PAYMENT
        // ======================
        function renderPayment() {

            $('#modalTitle').text('Payment');

            $('#modalBody').html(`
            ${renderMetaHeader(selectedMeta)}

            <div class="mb-3">
                <label>Amount</label>
                <input class="form-control" value="₹${selectedMeta.amount}" readonly>
            </div>

            <div class="mb-3">
                <label>Payment Method</label>
                <select class="form-select" id="paymentMethod">
                    <option value="">Select</option>
                    <option value="UPI">UPI</option>
                    <option value="WALLET">Wallet</option>
                    <option value="CARD">Card</option>
                    <option value="NETBANKING">Net Banking</option>
                </select>
            </div>
        `);

            $('#modalFooter').html(`
            <button class="btn buttonColor" id="payNowBtn">Pay Now</button>
            <button class="btn btn-secondary" data-bs-dismiss="modal" id="backToPlans">Cancel</button>
        `);
        }

        function renderMetaHeader(meta) {
            return `
            <div class="meta-card mb-3">
                <div><b>${meta.mobile || ''}</b></div>
                <small>${meta.operatorName || ''} | ${meta.circleName || ''} | ${meta.planName || ''}</small>
            </div>
        `;
        }

        // ======================
        // EVENTS
        // ======================

        // View Plans
        $(document).on('click', '#viewPlansBtn', function () {

            if (isFetching) return;

            const mobile = $('#mobile').val();
            const operator_id = $('#operator').val();
            const circle_id = $('#circle').val();
            const plan_type = $('#planType').val();

            if (!mobile || !operator_id || !circle_id || !plan_type) {
                alert('Fill all fields');
                return;
            }

            selectedMeta = {
                mobile,
                operator_id,
                operatorName: $('#operator option:selected').text(),
                circle_id,
                circleName: $('#circle option:selected').text(),
                plan_id: plan_type,
                planName: $('#planType option:selected').text()
            };

            isFetching = true;

            $('#modalBody').html(spinLoader('Fetching plans...'));

            $.ajax({
                url: `/user/bbps-recharge/getPlans/${operator_id}/${circle_id}/${plan_type}`,
                type: 'POST',
                data: { mobile },

                success: function (res) {

                    isFetching = false;

                    if (!res.success) {
                        alert(res.message || 'Plans not available');
                        renderStep(1);
                        return;
                    }

                    cachedPlans = res.data || [];

                    renderStep(2);
                },
                error: function (xhr, status, error) {

                    isFetching = false;

                    let message = 'Something went wrong';

                    if (xhr.responseJSON) {

                        if (xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }

                        else if (xhr.responseJSON.errors) {
                            const firstKey = Object.keys(xhr.responseJSON.errors)[0];
                            message = xhr.responseJSON.errors[firstKey][0];
                        }

                        else {
                            message = JSON.stringify(xhr.responseJSON);
                        }

                    } else if (xhr.responseText) {
                        message = xhr.responseText;
                    } else if (error) {
                        message = error;
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        html: `<p>${message}</p>`,
                        confirmButtonText: 'OK'
                    });

                    renderStep(1);
                }
            });
        });

        // Back
        $(document).on('click', '#backToForm', () => renderStep(1));


    })();

    function handlePayNowClick() {

        openMpinModal(function (mpin) {

            $('#modalBody').html(spinLoader('Processing payment...'));

            $.ajax({
                url: "{{route('mobile_prepaid_payment')}}",
                type: 'POST',
                contentType: 'application/json',

                data: JSON.stringify({
                    mobile: window.selectedMeta.mobile,
                    operator_id: window.selectedMeta.operator_id,
                    circle_id: window.selectedMeta.circle_id,
                    plan_id: window.selectedMeta.plan_id,
                    amount: window.selectedMeta.amount,
                    mpin: mpin
                }),

                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },

                success: function (res) {

                    if (res.status) {
                        $('#rechargeModal').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'Recharge Successful',
                            html: `<p>${res.message || 'Your recharge was completed successfully.'}</p>`,
                            confirmButtonText: 'OK'
                        });
                    } else {
                        $('#modalBody').html(`
                        <div class="text-center text-danger my-4">
                            ${res.message}
                        </div>
                    `);
                        $('#modalFooter').html(`
                        <button class="btn btn-secondary" data-bs-dismiss="modal" id="backToPlans">Cancel</button>
                    `);
                    }
                },

                error: function (xhr) {

                    let message = xhr.responseJSON?.message || 'Payment failed';

                    $('#modalBody').html(`
                    <div class="text-center text-danger my-4">
                        ${message}
                    </div>
                `);
                    $('#modalFooter').html(`
                    <button class="btn btn-secondary" data-bs-dismiss="modal" id="backToPlans">Cancel</button>
                `);
                }
            });
        });
    }

    function bindPaymentEvents() {
        $('#rechargeModal').off('click', '#payNowBtn').on('click', '#payNowBtn', handlePayNowClick);
    }

    $(document).on('click', '#payNowBtn', handlePayNowClick);
    $('#payNowBtn').prop('disabled', true).text('Processing...');

</script>