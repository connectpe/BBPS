<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance | RAFI FINTECH PRIVATE LIMITED</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f9fafb;
        }

        /* --- Dynamic Left Side Animations --- */
        @keyframes mesh-move {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }

        .animate-mesh {
            background: linear-gradient(-45deg, #4338ca, #6366f1, #a855f7, #4338ca);
            background-size: 400% 400%;
            animation: mesh-move 12s ease infinite;
        }

        @keyframes float-alt {

            0%,
            100% {
                transform: translate(0, 0);
            }

            33% {
                transform: translate(10px, -15px);
            }

            66% {
                transform: translate(-10px, 10px);
            }
        }

        .float-slow {
            animation: float-alt 8s ease-in-out infinite;
        }

        /* Fade Animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(15px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in {
            animation: fadeIn 0.8s ease forwards;
            opacity: 0;
        }

        .delay-1 {
            animation-delay: 0.2s;
        }

        .delay-2 {
            animation-delay: 0.4s;
        }

        /* Spinner */
        .spinner {
            border: 2px solid rgba(79, 70, 229, 0.1);
            border-top: 2px solid #4f46e5;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>
</head>

<body class="flex flex-col md:flex-row min-h-screen overflow-x-hidden">

    <div class="relative w-full md:w-1/2 animate-mesh text-white flex flex-col p-8 md:p-16 overflow-hidden">

        <div class="absolute top-[-10%] left-[-10%] w-64 h-64 bg-white/20 rounded-full blur-3xl float-slow"></div>
        <div class="absolute bottom-[-5%] right-[-5%] w-72 h-72 bg-purple-400/30 rounded-full blur-3xl float-slow"
            style="animation-delay: 2s;"></div>

        <div class="flex items-center gap-3 z-10 fade-in delay-1">
            <div class="bg-white/20 backdrop-blur-md p-2 rounded-lg">
                <img src="{{ asset('logo.png') }}" alt="Logo" class="w-8 h-8">
            </div>
            <span class="text-lg font-bold tracking-tight">RAFI FINTECH</span>
        </div>

        <div class="z-10 mt-12 md:my-auto fade-in delay-2">
            <h1 class="text-4xl md:text-6xl font-black mb-6 leading-tight">
                Better things <br>are <span class="text-indigo-200">coming.</span>
            </h1>
            <p class="text-indigo-100 text-lg max-w-md mb-10 leading-relaxed opacity-90">
                We're fine-tuning our systems to provide you with a more seamless financial experience.
            </p>

            <div class="flex flex-wrap gap-3 max-w-lg">
                <span
                    class="flex items-center px-4 py-2 bg-white/5 backdrop-blur-md border border-white/10 rounded-full text-[10px] md:text-xs font-semibold tracking-wide uppercase">
                    <span class="status-dot"></span> Payment Gateway Sync
                </span>
                <span
                    class="flex items-center px-4 py-2 bg-white/5 backdrop-blur-md border border-white/10 rounded-full text-[10px] md:text-xs font-semibold tracking-wide uppercase">
                    <span class="status-dot"></span> KYC Module Update
                </span>
                <span
                    class="flex items-center px-4 py-2 bg-white/5 backdrop-blur-md border border-white/10 rounded-full text-[10px] md:text-xs font-semibold tracking-wide uppercase">
                    <span class="status-dot"></span> Wallet Infrastructure
                </span>
                <span
                    class="flex items-center px-4 py-2 bg-white/5 backdrop-blur-md border border-white/10 rounded-full text-[10px] md:text-xs font-semibold tracking-wide uppercase">
                    <span class="status-dot"></span> Card Issuance API
                </span>
                <span
                    class="flex items-center px-4 py-2 bg-white/5 backdrop-blur-md border border-white/10 rounded-full text-[10px] md:text-xs font-semibold tracking-wide uppercase">
                    <span class="status-dot"></span> Payout Engine
                </span>
                <span
                    class="flex items-center px-4 py-2 bg-white/5 backdrop-blur-md border border-white/10 rounded-full text-[10px] md:text-xs font-semibold tracking-wide uppercase">
                    <span class="status-dot"></span> Fraud Detection AI
                </span>
            </div>
        </div>

        <div class="hidden md:block z-10 mt-auto pt-8 opacity-60 text-xs">
            © {{ date('Y') }} RAFI FINTECH PRIVATE LIMITED. ALL RIGHTS RESERVED.
        </div>
    </div>

    <div class="w-full md:w-1/2 bg-white flex flex-col justify-center items-center p-8 md:p-16">
        <div class="max-w-md w-full">

            <div class="flex items-center gap-3 mb-8 bg-indigo-50 w-fit px-4 py-2 rounded-full">
                <div class="spinner"></div>
                <span class="text-indigo-700 text-xs font-bold uppercase tracking-widest">Maintenance Mode</span>
            </div>

            <h2 class="text-3xl font-bold text-gray-900 mb-4">We'll be back shortly</h2>
            <p class="text-gray-500 mb-10 leading-relaxed">
                Our site is currently down for scheduled maintenance. Don't worry, your data is safe and we'll be back
                online in a few moments.
            </p>

            <div class="space-y-4">
                <div
                    class="p-6 rounded-2xl border border-gray-100 bg-gray-50/50 hover:bg-white hover:shadow-xl hover:shadow-indigo-500/5 transition-all duration-300">
                    <h3 class="text-xs font-bold text-gray-400 uppercase mb-4 tracking-widest">Get in touch</h3>

                    <a href="mailto:support@rafifintech.com" class="flex items-center gap-4 mb-4 group">
                        <div
                            class="w-10 h-10 bg-indigo-600 text-white rounded-xl flex items-center justify-center shadow-lg shadow-indigo-200">
                            📧
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-800">support@rafifintech.com</p>
                            <p class="text-xs text-gray-400">Response within 2 hours</p>
                        </div>
                    </a>

                    <div class="flex items-center gap-4 group">
                        <div
                            class="w-10 h-10 bg-white border border-gray-100 text-gray-600 rounded-xl flex items-center justify-center shadow-sm">
                            📞
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-800">+91 98765 43210</p>
                            <p class="text-xs text-gray-400">Available 9am - 6pm</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="md:hidden mt-16 text-center opacity-40 text-[10px] uppercase tracking-widest">
                © {{ date('Y') }} RAFI FINTECH PRIVATE LIMITED
            </div>
        </div>
    </div>

</body>

</html>