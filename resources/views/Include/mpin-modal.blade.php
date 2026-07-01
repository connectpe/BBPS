<style>
    .mpin-box {
        width: 50px;
        height: 55px;
        font-size: 22px;
        border-radius: 10px;
    }
</style>

<!-- MPIN modal -->
<div class="modal fade" id="mpinModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content p-3">

            <div class="modal-header border-0">

                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body text-center">

                <p class="text-muted mb-3">
                <h5 class="">Enter MPIN</h5>
                Secure your transaction with MPIN
                </p>

                <!-- MPIN INPUT BOXES -->
                <div class="d-flex justify-content-center gap-2 mb-3">
                    <input type="password" maxlength="1" class="mpin-box form-control text-center" />
                    <input type="password" maxlength="1" class="mpin-box form-control text-center" />
                    <input type="password" maxlength="1" class="mpin-box form-control text-center" />
                    <input type="password" maxlength="1" class="mpin-box form-control text-center" />
                </div>

                <div id="mpinError" class="text-danger small d-none">
                    Enter valid 4 digit MPIN
                </div>

            </div>

            <div class="modal-footer border-0">
                <!-- <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button> -->
                <button class="btn buttonColor w-100" id="submitMpinBtn">
                    Verify & Pay
                </button>
            </div>

        </div>
    </div>
</div>


<script>
    $(document).ready(function () {
        bindMpinEvents();
    });

    function handleMpinSubmit() {

        let mpin = '';

        $('.mpin-box').each(function () {
            mpin += $(this).val();
        });

        // Validation
        if (!/^\d{4}$/.test(mpin)) {
            $('#mpinError').removeClass('d-none');
            return;
        }

        $('#mpinError').addClass('d-none');

        // Verify MPIN
        $.post("{{route('verify_mpin')}}", { mpin: mpin }, function (res) {

            if (res.status) {

                $('#mpinModal').modal('hide');

                if (typeof window.pendingPaymentCallback === 'function') {
                    window.pendingPaymentCallback(mpin);
                }

            } else {
                $('#mpinError')
                    .text(res.message || 'Invalid MPIN')
                    .removeClass('d-none');
            }

        }).fail(function (xhr) {

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
            }

            $('#mpinError')
                .text(message)
                .removeClass('d-none');
        });
    }

    function bindMpinEvents() {

        $('#submitMpinBtn').off('click').on('click', function () {
            handleMpinSubmit();
        });

    }

    function openMpinModal(callback) {
        window.pendingPaymentCallback = callback;

        $('.mpin-box').val('');
        $('#mpinError').addClass('d-none');

        const modal = new bootstrap.Modal(document.getElementById('mpinModal'));
        modal.show();
    }

    $(document).on('input', '.mpin-box', function () {

        let value = $(this).val();

        if (!/^[0-9]$/.test(value)) {
            $(this).val('');
            return;
        }

        $(this).next('.mpin-box').focus();
    });

    // Backspace handling
    $(document).on('keydown', '.mpin-box', function (e) {
        if (e.key === "Backspace" && !$(this).val()) {
            $(this).prev('.mpin-box').focus();
        }
    });
</script>