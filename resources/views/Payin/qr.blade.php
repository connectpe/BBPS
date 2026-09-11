<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Rafifintech - Secure Payment</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

    <style>
        .payment-logo {
            object-fit: contain;
        }

        #qrcode img {
            display: block;
            margin: auto;
        }
    </style>
</head>

<body
    class="min-h-screen bg-gradient-to-br from-slate-950 via-indigo-950 to-slate-950 flex items-center justify-center px-4 py-8 font-sans">

    <div class="w-full max-w-5xl">

        <!-- Main Payment Card -->
        <div id="payment-card"
            class="bg-white rounded-[2rem] shadow-2xl shadow-black/30 overflow-hidden border border-white/20">

            <!-- Header -->
            <div class="px-6 sm:px-8 py-5 border-b border-slate-100 bg-white">

                <div class="flex items-center justify-between gap-4">

                    <!-- Company -->
                    <div class="flex items-center gap-3">

                        <!-- Company Logo -->
                        <div
                            class="w-12 h-12 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-center overflow-hidden shrink-0">

                            <!-- Replace this path with your actual Rafifintech logo -->
                            <img src="{{ asset('assets/image/Logo/Rafi-logo.jpeg') }}" alt="Rafifintech Pvt. Ltd."
                                class="w-full h-full payment-logo p-1">

                        </div>

                        <div>
                            <h1 class="text-lg sm:text-xl font-black text-slate-900 leading-tight">
                                Rafifintech Pvt. Ltd.
                            </h1>

                            <p class="text-xs text-slate-500 mt-0.5">
                                Secure Payment Gateway
                            </p>
                        </div>

                    </div>

                    <!-- Secure Badge -->
                    <div
                        class="hidden sm:flex items-center gap-2 px-3 py-2 rounded-xl bg-emerald-50 border border-emerald-100">

                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-emerald-600" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">

                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 15v2m-6 4h12a2 2 0 002-2v-7a2 2 0 00-2-2H6a2 2 0 00-2 2v7a2 2 0 002 2zm10-11V7a4 4 0 00-8 0v1" />

                        </svg>

                        <span class="text-xs font-semibold text-emerald-700">
                            Secure Payment
                        </span>

                    </div>

                </div>

            </div>


            <!-- Main Content -->
            <div class="grid grid-cols-1 lg:grid-cols-2">


                <!-- LEFT SIDE -->
                <div class="p-6 sm:p-8 lg:p-10">

                    <!-- Payment Details Heading -->
                    <div class="mb-7">

                        <p class="text-xs font-bold uppercase tracking-widest text-indigo-500">
                            Payment Details
                        </p>

                        <h2 class="text-2xl sm:text-3xl font-black text-slate-900 mt-1">
                            Complete Your Payment
                        </h2>

                        <p class="text-sm text-slate-500 mt-2">
                            Review the payment details and scan the QR code using any UPI app.
                        </p>

                    </div>


                    <!-- Amount -->
                    <div class="rounded-2xl bg-indigo-50 border border-indigo-100 p-5 mb-5">

                        <p class="text-xs font-semibold uppercase tracking-wider text-indigo-500">
                            Amount to Pay
                        </p>

                        <div class="flex items-baseline mt-1">

                            <span class="text-2xl font-bold text-indigo-600 mr-1">
                                ₹
                            </span>

                            <span class="text-4xl sm:text-5xl font-black tracking-tight text-slate-900">
                                {{ number_format($transaction->amount, 2) }}
                            </span>

                        </div>

                    </div>


                    <!-- Transaction Information -->
                    <div class="space-y-3 mb-6">

                        <div
                            class="flex items-center justify-between gap-4 px-4 py-3 rounded-xl bg-slate-50 border border-slate-100">

                            <span class="text-xs font-medium text-slate-500">
                                Client Reference ID
                            </span>

                            <span
                                class="font-mono text-xs font-bold text-slate-700 bg-white border border-slate-200 px-2.5 py-1.5 rounded-lg break-all text-right">
                                {{ $transaction->cust_txn_id ?? null }}
                            </span>

                        </div>


                    </div>


                    <!-- Timer -->
                    <div id="timer-container"
                        class="flex items-center justify-between bg-amber-50 border border-amber-200 rounded-2xl px-4 py-3">

                        <div class="flex items-center gap-2.5">

                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-amber-600 animate-pulse"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">

                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />

                            </svg>

                            <span class="text-xs font-semibold text-amber-800">
                                Session Expires In
                            </span>

                        </div>

                        <span id="countdown" class="text-sm font-black text-amber-900 tracking-wider">
                            08:00
                        </span>

                    </div>


                    <!-- Anti Refresh -->
                    <p
                        class="text-[11px] font-medium text-amber-500 text-center mt-2 tracking-wide flex items-center justify-center gap-1">

                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 flex-shrink-0" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">

                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />

                        </svg>

                        Please do not refresh or close this tab.
                    </p>


                    <!-- Default Instruction -->
                    <div id="default-instruction" class="mt-6 bg-slate-50 border border-slate-200 rounded-2xl p-4">

                        <div class="flex gap-3">

                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-indigo-600 flex-shrink-0"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">

                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M13 16h-1v-4h-1m1-4h.01M12 20a8 8 0 100-16 8 8 0 000 16z" />

                            </svg>

                            <p class="text-xs leading-5 text-slate-600 text-left">
                                Open your UPI app, scan the secure QR code,
                                and verify the amount before approving payment.
                            </p>

                        </div>

                    </div>


                    <!-- Expiry Warning -->
                    <div id="expiry-warning-banner"
                        class="mt-4 bg-rose-50 border border-rose-200 rounded-2xl p-4 hidden transition-all animate-bounce">

                        <div class="flex gap-3">

                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-rose-600 flex-shrink-0 mt-0.5"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">

                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77-1.333.192 3 1.732 3z" />

                            </svg>

                            <p class="text-xs font-semibold leading-5 text-rose-800 text-left">
                                Session expired! Please do not make any payment against
                                this QR to avoid dual or failed transfers.
                                Refresh or generate a new link.
                            </p>

                        </div>

                    </div>

                </div>


                <!-- RIGHT SIDE -->
                <div
                    class="bg-slate-50 border-t lg:border-t-0 lg:border-l border-slate-100 p-6 sm:p-8 lg:p-10 flex flex-col items-center justify-center">

                    <!-- QR Heading -->
                    <div class="text-center mb-5">

                        <p class="text-xs font-bold uppercase tracking-widest text-indigo-500">
                            Scan & Pay
                        </p>

                        <h2 class="text-xl sm:text-2xl font-black text-slate-900 mt-1">
                            Scan QR Code
                        </h2>

                        <p class="text-xs text-slate-500 mt-1">
                            Use any supported UPI app
                        </p>

                    </div>


                    <!-- QR Container -->
                    <div class="relative bg-white rounded-3xl p-5 border border-slate-200 shadow-lg">

                        <div id="qrcode" class="bg-white p-2 rounded-2xl inline-block">
                        </div>


                        <!-- Expired Overlay -->
                        <div id="qr-expired-overlay"
                            class="absolute inset-0 bg-slate-900/85 backdrop-blur-sm rounded-3xl flex flex-col items-center justify-center text-center p-4 hidden">

                            <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-rose-400 mb-2"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">

                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333-.77 2.694 3.464 0z" />

                            </svg>

                            <p class="text-white text-xs font-bold">
                                QR Code Expired
                            </p>

                        </div>

                    </div>


                    <!-- Scan Text -->
                    <div class="text-center mt-6">

                        <div class="flex items-center justify-center gap-2">

                            <span class="w-8 h-px bg-slate-300"></span>

                            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">
                                Scan by any UPI app
                            </span>

                            <span class="w-8 h-px bg-slate-300"></span>

                        </div>

                    </div>


                    <!-- UPI Apps -->
                    <div class="flex items-center justify-center gap-4 mt-5 flex-wrap">

                        <!-- Google Pay -->
                        <div class="w-14 h-14 bg-white rounded-2xl border border-slate-200 shadow-sm flex items-center justify-center p-2"
                            title="Google Pay">

                            <img src="https://cdn.simpleicons.org/googlepay" alt="Google Pay"
                                class="w-9 h-9 object-contain">

                        </div>


                        <!-- PhonePe -->
                        <div class="w-14 h-14 bg-white rounded-2xl border border-slate-200 shadow-sm flex items-center justify-center p-2"
                            title="PhonePe">

                            <img src="https://cdn.simpleicons.org/phonepe" alt="PhonePe"
                                class="w-9 h-9 object-contain">

                        </div>


                        <!-- Paytm -->
                        <div class="w-14 h-14 bg-white rounded-2xl border border-slate-200 shadow-sm flex items-center justify-center p-2"
                            title="Paytm">

                            <img src="https://cdn.simpleicons.org/paytm" alt="Paytm"
                                class="w-9 h-9 object-contain">

                        </div>


                    </div>


                    <p class="text-[11px] text-slate-400 text-center mt-4">
                        Scan the QR code and complete your payment securely.
                    </p>

                </div>

            </div>


            <!-- Footer -->
            <div
                class="px-6 sm:px-8 py-4 bg-white border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-2">

                <div class="flex items-center gap-2">

                    <div
                        class="w-7 h-7 rounded-lg bg-slate-50 border border-slate-200 flex items-center justify-center overflow-hidden">

                        <img src="{{ asset('assets/image/Logo/Rafi-logo.jpeg') }}" alt="Rafifintech"
                            class="w-full h-full object-contain p-0.5">

                    </div>

                    <span class="text-xs font-bold text-slate-600">
                        Rafifintech Pvt. Ltd.
                    </span>

                </div>


                <div class="flex items-center gap-2 text-slate-400">

                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-emerald-500" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">

                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-7a2 2 0 00-2-2H6a2 2 0 00-2 2v7a2 2 0 002 2zm10-11V7a4 4 0 00-8 0v1" />

                    </svg>

                    <span class="text-[11px] font-medium">
                        Secure & Encrypted Payment
                    </span>

                </div>

            </div>

        </div>


        <!-- Security Badge -->
        <div class="flex items-center justify-center gap-2 mt-5 text-slate-400">

            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-emerald-400" fill="none"
                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">

                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M12 15v2m-6 4h12a2 2 0 002-2v-7a2 2 0 00-2-2H6a2 2 0 00-2 2v7a2 2 0 002 2zm10-11V7a4 4 0 00-8 0v1" />

            </svg>

            <span class="text-xs font-medium tracking-wide">
                256-Bit Encrypted Payment Ecosystem
            </span>

        </div>

    </div>


    <!-- Scripts -->
    <script>
        const qrUrl = @json($transaction->upi_intent);
        const txnId = @json($transaction->txn_id);


        // Render QR Code
        new QRCode(document.getElementById("qrcode"), {
            text: qrUrl,
            width: 210,
            height: 210,
            correctLevel: QRCode.CorrectLevel.M
        });


        // Persistent 8-Minute Countdown Timer Logic using localStorage
        const storageKey = `groscope_payment_timer_${txnId}`;
        const durationSeconds = 8 * 60;

        let expirationTime = localStorage.getItem(storageKey);
        const now = Date.now();

        if (!expirationTime || now >= parseInt(expirationTime, 10)) {

            expirationTime = now + (durationSeconds * 1000);

            localStorage.setItem(storageKey, expirationTime);

        } else {

            expirationTime = parseInt(expirationTime, 10);

        }


        const countdownElement = document.getElementById("countdown");
        const timerContainer = document.getElementById("timer-container");
        const qrExpiredOverlay = document.getElementById("qr-expired-overlay");
        const expiryWarningBanner = document.getElementById("expiry-warning-banner");
        const defaultInstruction = document.getElementById("default-instruction");


        function updateTimer() {

            const currentTime = Date.now();

            let remainingSeconds =
                Math.floor((expirationTime - currentTime) / 1000);


            if (remainingSeconds <= 0) {

                clearInterval(timerInterval);

                countdownElement.textContent = "00:00";


                // Trigger Expired State UI Modifications
                timerContainer.classList.remove(
                    "bg-amber-50",
                    "border-amber-200"
                );

                timerContainer.classList.add(
                    "bg-rose-50",
                    "border-rose-200"
                );


                countdownElement.classList.remove(
                    "text-amber-900"
                );

                countdownElement.classList.add(
                    "text-rose-600"
                );


                qrExpiredOverlay.classList.remove("hidden");

                expiryWarningBanner.classList.remove("hidden");

                defaultInstruction.classList.add("hidden");


                // Clear storage on full expiry
                localStorage.removeItem(storageKey);

                return;
            }


            let minutes = Math.floor(remainingSeconds / 60);
            let seconds = remainingSeconds % 60;


            countdownElement.textContent =
                String(minutes).padStart(2, '0') +
                ":" +
                String(seconds).padStart(2, '0');
        }


        // Run immediately then every second
        updateTimer();

        const timerInterval = setInterval(updateTimer, 1000);
    </script>

</body>

</html>
