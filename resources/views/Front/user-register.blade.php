<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>BBPS Portal</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Sweetalert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body,
        html {
            height: 100%;
            margin: 0;
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea, #764ba2);
        }

        .container-full {
            height: 100vh;
        }

        .left-side {
            position: relative;
            overflow: hidden;
            border-radius: 20px 0 0 20px;
        }

        .left-side img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .left-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(102, 126, 234, 0.6);
            /* semi-transparent gradient overlay */
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding-left: 30px;
            color: #fff;
        }

        .left-overlay h1 {
            font-size: 2.5rem;
            font-weight: 700;
        }

        .left-overlay p {
            font-size: 1.1rem;
            margin-top: 10px;
        }

        .form-container {
            max-width: 400px;
            width: 100%;
            background: rgba(255, 255, 255, 0.95);
            padding: 40px 30px;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            color: #333;
        }

        .bbps-btn {
            background: linear-gradient(to right, #667eea, #764ba2);
            color: #fff;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .bbps-btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }

        .form-floating input {
            border-radius: 10px;
        }

        .otp-input {
            width: 50px;
            height: 50px;
            text-align: center;
            font-size: 1.5rem;
            margin: 0 5px;
            border-radius: 10px;
            border: none;
            outline: none;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.15);
        }

        .toggle-btns .btn {
            border-radius: 50px;
            font-weight: 600;
        }

        @media (max-width: 768px) {
            .left-side {
                display: none;
            }
        }
    </style>
</head>

<body>

    <div class="container-fluid vh-100">
        <div class="row h-100">

            <!-- Left Side Image -->
            <div class="col-md-6 d-none d-md-block position-relative left-side" style="border-radius: 20px 0 0 20px; overflow:hidden;">
                <div style="position:absolute; top:0; left:0; width:100%; height:100%; background: url('{{asset('assets/image/pay-image.jpg')}}') no-repeat center center; background-size: cover; filter: brightness(0.8); z-index:1;"></div>
                <div style="position:absolute; top:0; left:0; width:100%; height:100%; background: linear-gradient(135deg, rgba(102,126,234,0.7), rgba(118,75,162,0.7)); z-index:2;"></div>
                <div style="position:absolute; top:50%; left:50%; transform: translate(-50%, -50%); color:#fff; text-align:center; padding: 0 30px; z-index:3;">
                    <h1 style="font-size:2.8rem; font-weight:700;">Welcome to BBPS Portal</h1>
                    <p style="font-size:1.2rem; margin-top:15px; line-height:1.5;">Pay your bills, recharge utilities, and manage payments securely and easily.</p>
                </div>
            </div>

            <!-- Right Side Form -->
            <div class="col-md-6 d-flex flex-column align-items-center justify-content-center p-4 position-relative">

                <!-- Toggle Buttons at top-right corner -->


                <!-- Form Container -->
                <div class="form-container w-100" style="max-width:400px;">

                    <!-- Login Form -->
                    <form id="loginForm">
                        <h3 class="text-center mb-2" style="color:#667eea;">Login to your account</h3>
                        <p class="text-center text-muted mb-4">Login to access your panel</p>
                        <div class="mb-3 form-floating">
                            <input type="email" class="form-control" id="loginEmail" placeholder="Email">
                            <label for="loginEmail">Email</label>
                        </div>
                        <div class="mb-4 form-floating">
                            <input type="password" class="form-control" id="loginPassword" placeholder="Password">
                            <label for="loginPassword">Password</label>
                        </div>
                        <button type="submit" class="btn bbps-btn w-100">Login</button>
                        <p class="text-center mt-3 text-muted">
                            Don't have an account?
                            <a href="#" id="switchToSignup" style="color:#667eea; text-decoration:none; font-weight:500;">SignUp</a>
                        </p>
                    </form>

                    <!-- SignUp Form -->
                    <form id="signupForm" class="d-none">
                        <h3 class="text-center mb-2" style="color:#667eea;">Create a new account</h3>
                        <p class="text-center text-muted mb-3">Register to pay your bills quickly</p>
                        <div class="mb-3 form-floating">
                            <input type="text" class="form-control" id="signupName" placeholder="Name">
                            <label for="signupName">Name</label>
                        </div>
                        <div class="mb-3 form-floating">
                            <input type="email" class="form-control" id="signupEmail" placeholder="Email">
                            <label for="signupEmail">Email</label>
                        </div>
                        <div class="mb-3 form-floating">
                            <input type="text" class="form-control" id="signupMobile" placeholder="Mobile">
                            <label for="signupMobile">Mobile</label>
                        </div>
                        <button type="submit" class="btn bbps-btn w-100">Sign Up</button>
                        <p class="text-center mt-2 text-muted">
                            Already have an account?
                            <a href="#" id="switchToLogin" style="color:#667eea; text-decoration:none; font-weight:500;">Login</a>
                        </p>
                    </form>

                    <!-- OTP Form -->
                    <form id="otpForm" class="d-none text-center mt-3">
                        <p style="color:#667eea;">Enter the 6-digit OTP sent to your email</p>
                        <div class="d-flex justify-content-center mb-3">
                            <input type="text" maxlength="1" class="otp-input">
                            <input type="text" maxlength="1" class="otp-input">
                            <input type="text" maxlength="1" class="otp-input">
                            <input type="text" maxlength="1" class="otp-input">
                            <input type="text" maxlength="1" class="otp-input">
                            <input type="text" maxlength="1" class="otp-input">
                        </div>
                        <button type="submit" class="btn bbps-btn w-100">Verify OTP</button>
                        <p class="text-center mt-2 text-muted">
                            Already have an account?
                            <a href="#" id="switchLogin" style="color:#667eea; text-decoration:none; font-weight:500;">Login</a>
                        </p>
                    </form>

                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const loginForm = document.getElementById('loginForm');
            const signupForm = document.getElementById('signupForm');
            const otpForm = document.getElementById('otpForm');
            const switchToSignup = document.getElementById('switchToSignup');
            const switchToLogin = document.getElementById('switchToLogin');
            const switchLogin = document.getElementById('switchLogin');

            function showLogin() {
                loginForm.classList.remove('d-none');
                signupForm.classList.add('d-none');
                otpForm.classList.add('d-none');
            }

            function showSignup() {
                signupForm.classList.remove('d-none');
                loginForm.classList.add('d-none');
                otpForm.classList.add('d-none');
            }

            function showOTP() {
                otpForm.classList.remove('d-none');
            }

            // Dummy signup submission
            signupForm.addEventListener('submit', (e) => {
                e.preventDefault();
                const response = true; // simulate backend success
                if (response) {
                    signupForm.classList.add('d-none');
                    otpForm.classList.remove('d-none');
                    showOTP();
                    otpInputs[0].focus();
                } else {
                    Swal.fire('Error', 'Signup failed. Try again.', 'error');
                }
            });

            // OTP input navigation
            const otpInputs = document.querySelectorAll('.otp-input');
            otpInputs.forEach((input, i) => {
                input.addEventListener('input', () => {
                    if (input.value.length === 1 && i < otpInputs.length - 1) otpInputs[i + 1].focus();
                });
                input.addEventListener('keydown', (e) => {
                    if (e.key === 'Backspace' && input.value === '' && i > 0) otpInputs[i - 1].focus();
                });
            });

            // Do not have account
            switchToSignup.addEventListener('click', (e) => {
                e.preventDefault();
                showSignup();
            });

            // Already have a account
            switchToLogin.addEventListener('click', (e) => {
                e.preventDefault();
                showLogin();
            });

            // Already have a account
            switchLogin.addEventListener('click', (e) => {
                e.preventDefault();
                showLogin();
            });


        });
    </script>

</body>

</html>