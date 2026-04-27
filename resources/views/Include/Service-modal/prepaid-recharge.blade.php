<!-- Recharge Modal -->
<div class="modal fade" id="rechargeModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header position-relative overflow-visible">
                <h5 class="modal-title" id="modalTitle">
                    Recharge
                </h5>

                <img src="{{ asset('assets/image/Logo/bharat-connect-logo.jpg') }}" alt="" class="position-absolute"
                    style="top: 10px; right: 50px; width: 70px; z-index: 1060;">

                <button type="button" class="btn-close position-absolute bg-light" data-bs-dismiss="modal"
                    style="right: 18px; z-index: 1061;">
                </button>
            </div>

            <form id="rechargeForm">

                <div class="modal-body">

                    <!-- Mobile Number -->
                    <div class="mb-3">
                        <label class="form-label">Mobile Number</label>
                        <input type="text" class="form-control" id="mobile" placeholder="Enter mobile number">
                    </div>

                    <!-- Operator -->
                    <div class="mb-3">
                        <label class="form-label">Operator</label>
                        <select class="form-select" id="operator">
                            <option value="">Select Operator</option>
                            @foreach ($rechargeOperators as $op)
                            <option value="{{ $op['op_id'] }}">{{ $op['name'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Circle -->
                    <div class="mb-3">
                        <label class="form-label">Circle</label>
                        <select class="form-select" id="circle">
                            <option value="">Select Circle</option>
                            @foreach ($rechargeCircles as $circle)
                            <option value="{{ $circle['circle_id'] }}">
                                {{ $circle['name'] }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Plan Type -->
                    <div class="mb-3">
                        <label class="form-label">Plan Type</label>
                        <select class="form-select" id="planType">
                            <option value="">Select Plan Type</option>
                            @foreach ($rechargePlanTypes as $plan)
                            <option value="{{ $plan['plan_id'] }}">
                                {{ $plan['name'] }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="nextBtn">
                        View Plans
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>


<script>

    (function () {

        let isFetching = false;
        let requestId = 0;
        let abortController = null;

        // Use delegated event (safe for modal)
        $(document).off('click', '#nextBtn').on('click', '#nextBtn', function () {

            if (isFetching) return;

            const mobile = $('#mobile').val();
            const operator_id = $('#operator').val();
            const circle_id = $('#circle').val();
            const plan_type = $('#planType').val();

            // Validation
            if (!mobile || !operator_id || !circle_id || !plan_type) {
                alert('Please fill all fields');
                return;
            }

            isFetching = true;

            // Store selected meta (optional but useful)
            window.selectedMeta = {
                mobile,
                operator_id,
                operatorName: $('#operator option:selected').text(),
                circle_id,
                circleName: $('#circle option:selected').text(),
                plan_id: plan_type,
                planName: $('#planType option:selected').text(),
                amount: 0,
            };

            window.cachedPlans = [];

            // Abort previous request
            if (abortController) {
                try { abortController.abort(); } catch (e) { }
            }

            abortController = new AbortController();
            const currentReq = ++requestId;

            $.ajax({
                url: '/user/bbps-recharge/getPlans/' + operator_id + '/' + circle_id + '/' + plan_type,
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    mobile,
                    operator_id,
                    circle_id,
                    plan_type
                }),
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },

                beforeSend: function () {
                    $('#modalBody').html(spinLoader('Fetching plans...'));
                },

                success: function (response) {

                    if (currentReq !== requestId) return;

                    isFetching = false;

                    if (!response.success) {
                        $('#modalBody').html(`
                            <div class="text-center text-danger my-4">
                                ${response.message || 'Plans not available'}
                            </div>
                        `);
                        return;
                    }

                    window.cachedPlans = response.data || [];

                    renderPlansList(window.cachedPlans, mobile);
                },

                error: function (xhr, status, error) {

                    if (status === 'abort') return;

                    console.error(error);
                    isFetching = false;

                    $('#modalBody').html(`
                        <div class="text-center text-danger my-4">
                            Error fetching plans
                        </div>
                    `);
                }
            });

        });

    })();

    function renderPlansList(plans, mobile) {

        let html = `
        ${renderMetaHeader(selectedMeta)}
        <div class="plans-wrap">
            <div class="plans-scroll">
    `;

        plans.forEach(p => {

            const amt = p.amount || 0;
            const validity = (p.validity || '').trim() || '—';
            let desc = (p.description || '').trim() || 'Plan details';

            if (p.talktime) {
                desc += ` • Talktime ₹${p.talktime}`;
            }

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

        // Plan selection
        $('.plan').off('click').on('click', function () {

            $('.plan').removeClass('active');
            $(this).addClass('active');

            const selectedAmount = $(this).data('amt') || 0;
            selectedMeta.amount = selectedAmount;

            console.log('Selected Plan:', selectedMeta);
        });
    }

    function renderMetaHeader(meta) {
        return `
                <div class="meta-card">
                    <div class="meta-top">
                        <div class="meta-title">
                            <div class="meta-ico"><i class="bi bi-receipt-cutoff"></i></div>
                            <div>
                                <div style="font-size:12px; opacity:.75;">Recharge Details</div>
                                <div class="meta-mobile">${meta.mobile || ''}</div>
                            </div>
                        </div>
                    </div>

                    <div class="meta-badges">
                        <div class="meta-badge"><i class="bi bi-sim"></i><span><b>Operator:</b> ${meta.operatorName || '-'}</span></div>
                        <div class="meta-badge"><i class="bi bi-geo-alt"></i><span><b>Circle:</b> ${meta.circleName || '-'}</span></div>
                        <div class="meta-badge"><i class="bi bi-lightning-charge"></i><span><b>Plan:</b> ${meta.planName || '-'}</span></div>
                    </div>
                </div>
            `;
    }
</script>