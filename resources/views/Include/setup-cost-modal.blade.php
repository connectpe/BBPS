<style>
    .blink-radio {
        display: inline-block;
        padding: 10px 20px;
        border: 2px solid #007bff;
        border-radius: 6px;
        cursor: pointer;
        background: #2339d6;
        color: #fff;
        font-weight: 600;
        transition: all 0.3s ease;
        animation: zoomShadow 1.2s infinite;
    }

    @keyframes zoomShadow {
        0% {
            box-shadow: 0 0 0 0 rgba(0, 123, 255, 0.6);
            transform: scale(1);
        }

        50% {
            box-shadow: 0 0 15px 5px rgba(0, 123, 255, 0.4);
            transform: scale(1.05);
        }

        100% {
            box-shadow: 0 0 0 0 rgba(0, 123, 255, 0);
            transform: scale(1);
        }
    }

    .blink-radio:hover {
        background: #007bff;
        color: #fff;
    }
</style>

<!-- Trigger -->
<div class="blink-radio" data-amount="{{ $amount ?? 1000 }}">Setup Cost</div>

<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow rounded">

            <div class="modal-header">
                <h5 class="modal-title">Pay Setup Cost</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Amount</span>
                    <span>₹<span id="amount"></span></span>
                </div>

                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">GST (18%)</span>
                    <span>₹<span id="gst"></span></span>
                </div>

                <hr>

                <div class="d-flex justify-content-between fw-bold fs-5">
                    <span>Total Amount</span>
                    <span class="text-success">₹<span id="total"></span></span>
                </div>

            </div>

            <div class="modal-footer d-flex justify-content-right">
                <button class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    Cancel
                </button>

                <button class="btn buttonColor px-4" id="payNowBtn">
                    Pay Now
                </button>
            </div>

        </div>
    </div>
</div>

<script>
    document.getElementById("payNowBtn").addEventListener("click", function() {

        let amount = parseInt(document.getElementById("amount").innerText);
        let gst = parseInt(document.getElementById("gst").innerText);
        let total = parseInt(document.getElementById("total").innerText);

        let formData = new FormData();
        formData.append('amount', amount);
        formData.append('gst', gst);
        formData.append('total', total);

        fetch("{{ route('payin.orders') }}", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                console.log(data);
            })
            .catch(err => console.log(err));
    });
</script>