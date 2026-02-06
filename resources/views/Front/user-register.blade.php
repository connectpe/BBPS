<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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

        .form-container {
            max-width: 400px;
            width: 100%;
            background: rgba(255, 255, 255, .95);
            padding: 40px 30px;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, .2);
            color: #333;
        }

        .bbps-btn {
            background: linear-gradient(to right, #667eea, #764ba2);
            color: #fff;
            font-weight: 600;
            transition: all .3s ease;
        }

        .bbps-btn:hover {
            opacity: .9;
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
            box-shadow: 0 3px 10px rgba(0, 0, 0, .15);
        }

        @media (max-width:768px) {
            .left-side {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="container-fluid vh-100">
        <div class="row h-100">

            <div class="col-md-6 d-none d-md-block position-relative left-side"
                style="border-radius: 20px 0 0 20px; overflow:hidden;">
                <div
                    style="position:absolute; top:0; left:0; width:100%; height:100%; background: url('{{ asset('assets/image/pay-image.jpg') }}') no-repeat center center; background-size: cover; filter: brightness(0.8); z-index:1;">
                </div>
                <div
                    style="position:absolute; top:0; left:0; width:100%; height:100%; background: linear-gradient(135deg, rgba(102,126,234,0.7), rgba(118,75,162,0.7)); z-index:2;">
                </div>
                <div
                    style="position:absolute; top:50%; left:50%; transform: translate(-50%, -50%); color:#fff; text-align:center; padding: 0 30px; z-index:3;">
                    <h1 style="font-size:2.8rem; font-weight:700;">Welcome to BBPS Portal</h1>
                    <p style="font-size:1.2rem; margin-top:15px; line-height:1.5;">Pay your bills, recharge utilities,
                        and manage payments securely and easily.</p>
                </div>
            </div>

            <div class="col-md-6 d-flex flex-column align-items-center justify-content-center p-4 position-relative">
                <div class="form-container w-100" style="max-width:400px;">

                    {{-- Login Form --}}
                    <form id="loginForm" action="{{ route('admin.login') }}" method="POST">
                        @csrf
                        <h3 class="text-center mb-2" style="color:#667eea;">Login to your account</h3>
                        <p class="text-center text-muted mb-4">Login to access your panel</p>

                        <div class="mb-3 form-floating">
                            <input type="email" name="email" class="form-control" id="loginEmail"
                                placeholder="Email">
                            <label for="loginEmail">Email</label>
                        </div>

                        <div class="mb-4 form-floating">
                            <input type="password" name="password" class="form-control" id="loginPassword"
                                placeholder="Password">
                            <label for="loginPassword">Password</label>
                        </div>

                        <button type="submit" class="btn bbps-btn w-100" id="loginButton">Login</button>

                        <p class="text-center mt-3 text-muted">
                            Don't have an account?
                            <a href="#" id="switchToSignup"
                                style="color:#667eea; text-decoration:none; font-weight:500;">SignUp</a>
                        </p>
                    </form>

                    {{-- SignUp Form --}}
                    <form id="signupForm" class="d-none">
                        @csrf
                        <h3 class="text-center mb-2" style="color:#667eea;">Create a new account</h3>
                        <p class="text-center text-muted mb-3">Register to pay your bills quickly</p>

                        <div class="mb-3 role-selector">
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-primary w-50 bbps-btn"
                                    data-role="user">User</button>
                                <button type="button" class="btn btn-outline-primary w-50"
                                    data-role="reseller">Reseller</button>
                            </div>
                            <input type="hidden" name="role" id="role" value="user">
                        </div>

                        <div class="mb-3 form-floating">
                            <input type="text" name="name" class="form-control" id="signupName" placeholder="Name"
                                required>
                            <span class="text-danger" id="nameError" style="font-size: 0.875em;"></span>
                            <label for="signupName">Name</label>
                        </div>

                        <div class="mb-3 form-floating">
                            <input name="email" type="email" class="form-control" id="signupEmail"
                                placeholder="Email" required>
                            <span class="text-danger" id="emailError" style="font-size: 0.875em;"></span>
                            <label for="signupEmail">Email</label>
                        </div>

                        <div class="mb-3 form-floating">
                            <input type="text" name="mobile" class="form-control" id="signupMobile"
                                placeholder="Mobile" required>
                            <span class="text-danger" id="mobileError" style="font-size: 0.875em;"></span>
                            <label for="signupMobile">Mobile</label>
                        </div>

                        <div class="mb-3 form-floating">
                            <input type="password" name="password" class="form-control" id="signuppassword"
                                placeholder="Password" required>
                            <span class="text-danger" id="passwordError" style="font-size: 0.875em;"></span>
                            <label for="signuppassword">Password</label>
                        </div>

                        <div class="mb-3 form-floating">
                            <input type="password" name="password_confirmation" class="form-control"
                                id="signuppassword_confirmation" placeholder="Confirm Password" required>
                            <span class="text-danger" id="password_confirmationError"
                                style="font-size: 0.875em;"></span>
                            <label for="signuppassword_confirmation">Confirm Password</label>
                        </div>

                        <button type="submit" class="btn bbps-btn w-100" id="signupButton">Sign Up</button>

                        <p class="text-center mt-2 text-muted">
                            Already have an account?
                            <a href="#" id="switchToLogin"
                                style="color:#667eea; text-decoration:none; font-weight:500;">Login</a>
                        </p>
                    </form>

                    {{-- OTP Form --}}
                    <form id="otpForm" class="d-none text-center mt-3">
                        @csrf
                        <h3 class="text-center mb-2" style="color:#667eea;">Verify Email</h3>
                        <p style="color:#667eea; font-size:0.9rem;">Enter the 4-digit OTP sent to your email</p>
                        <input type="hidden" id="otpEmail" name="email">

                        <div class="d-flex justify-content-center mb-4">
                            <input type="text" maxlength="1" class="otp-input form-control" inputmode="numeric"
                                required>
                            <input type="text" maxlength="1" class="otp-input form-control" inputmode="numeric"
                                required>
                            <input type="text" maxlength="1" class="otp-input form-control" inputmode="numeric"
                                required>
                            <input type="text" maxlength="1" class="otp-input form-control" inputmode="numeric"
                                required>
                        </div>

                        <button type="submit" class="btn bbps-btn w-100" id="otpVerifyBtn">Verify OTP</button>

                        <p class="text-center mt-3 text-muted">
                            <a href="#" id="switchLogin"
                                style="color:#667eea; text-decoration:none; font-weight:500;">Back to Login</a>
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

            const otpInputs = document.querySelectorAll('.otp-input');

            let userEmail = ''; 
            let pendingLoginEmail = ''; 
            let pendingLoginPassword = ''; 
            let isverfiy = false;

            function showLogin() {
                loginForm.classList.remove('d-none');
                signupForm.classList.add('d-none');
                otpForm.classList.add('d-none');
                clearErrors();
            }

            function showSignup() {
                signupForm.classList.remove('d-none');
                loginForm.classList.add('d-none');
                otpForm.classList.add('d-none');
                clearErrors();
            }

            function showOTP() {
                otpForm.classList.remove('d-none');
                signupForm.classList.add('d-none');
                loginForm.classList.add('d-none');
                otpInputs[0].focus();
            }

            function clearErrors() {
                ['name', 'email', 'mobile', 'password', 'password_confirmation'].forEach(f => {
                    const el = document.getElementById(f + 'Error');
                    if (el) el.textContent = '';
                });
            }

            document.querySelectorAll('.role-selector button').forEach(button => {
                button.addEventListener('click', function() {
                    document.querySelectorAll('.role-selector button').forEach(btn => btn.classList
                        .remove('bbps-btn'));
                    this.classList.add('bbps-btn');
                    document.getElementById('role').value = this.dataset.role;
                });
            });

            signupForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                clearErrors();

                const signupButton = document.getElementById('signupButton');
                signupButton.disabled = true;
                signupButton.textContent = 'Processing...';

                const formData = new FormData(signupForm);

                const payload = {
                    name: formData.get('name'),
                    email: formData.get('email'),
                    mobile: formData.get('mobile'),
                    role: formData.get('role'),
                    password: formData.get('password'),
                    password_confirmation: formData.get('password_confirmation'),
                };

                try {
                    const res = await fetch("{{ route('admin.signup') }}", {
                        method: "POST",
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .getAttribute('content'),
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(payload)
                    });

                    const data = await res.json();

                    if (res.ok && data.status) {
                        userEmail = payload.email;
                        document.getElementById('otpEmail').value = userEmail;

                        Swal.fire('Success', 'OTP sent to your email. Please verify.', 'success');
                        showOTP();
                    } else {
                        if (data.errors) {
                            Object.keys(data.errors).forEach(field => {
                                const el = document.getElementById(field + 'Error');
                                if (el) el.textContent = data.errors[field][0];
                            });
                        }
                        Swal.fire('Error', data.message || 'Signup failed', 'error');
                    }

                } catch (err) {
                    Swal.fire('Error', 'Server error. Try again later.', 'error');
                    console.error(err);
                }

                signupButton.disabled = false;
                signupButton.textContent = 'Sign Up';
            });

            otpInputs.forEach((input, i) => {
                input.addEventListener('input', (e) => {
                    e.target.value = e.target.value.replace(/[^0-9]/g, '');
                    if (e.target.value.length === 1 && i < otpInputs.length - 1) {
                        otpInputs[i + 1].focus();
                    }
                });
                input.addEventListener('keydown', (e) => {
                    if (e.key === 'Backspace' && input.value === '' && i > 0) {
                        otpInputs[i - 1].focus();
                    }
                });
            });

            

            loginForm.addEventListener('submit', async function(e) {
                e.preventDefault();

                const loginButton = document.getElementById('loginButton');
                loginButton.disabled = true;
                loginButton.textContent = 'Logging in...';

                pendingLoginEmail = document.getElementById('loginEmail').value;
                pendingLoginPassword = document.getElementById('loginPassword').value;

                try {
                    const res = await fetch(this.action, {
                        method: 'POST',
                        body: new FormData(this),
                        headers: {
                            'Accept': 'application/json'
                        }
                    });

                    const data = await res.json();

                    if (res.ok && data.status && data.isOtpSend) {
                        userEmail = data.email;
                        document.getElementById('otpEmail').value = data.email;

                        Swal.fire('OTP Sent', data.message || 'OTP sent to your email', 'success');
                        showOTP();
                        isverfiy = true;

                        loginButton.disabled = false;
                        loginButton.textContent = 'Login';
                        return;
                    }

                    if (res.ok && data.status) {
                        Swal.fire('Success', data.message || 'Login successful', 'success')
                            .then(() => window.location.href = data.redirect);
                        return;
                    }

                    if (res.status === 422 && data.errors) {
                        const firstKey = Object.keys(data.errors)[0];
                        Swal.fire('Error', data.errors[firstKey][0], 'error');
                    } else {
                        Swal.fire('Error', data.message || 'Login failed', 'error');
                    }

                } catch (err) {
                    Swal.fire('Error', 'Server error. Please try again later.', 'error');
                    console.error(err);
                }

                loginButton.disabled = false;
                loginButton.textContent = 'Login';
            });

            otpForm.addEventListener('submit', async (e) => {
                e.preventDefault();

                const otpBtn = document.getElementById('otpVerifyBtn');
                otpBtn.disabled = true;
                otpBtn.textContent = 'Verifying...';

                const otpValues = Array.from(otpInputs).map(i => i.value).join('');

                if (otpValues.length !== 4) {
                    Swal.fire('Error', 'Please enter a valid 4-digit OTP', 'error');
                    otpBtn.disabled = false;
                    otpBtn.textContent = 'Verify OTP';
                    return;
                }

                try {
                    const res = await fetch("{{ route('verify_otp') }}", {
                        method: "POST",
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .getAttribute('content'),
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            email: userEmail,
                            otp: otpValues
                        })
                    });

                    const data = await res.json();

                   

                    if (!(data.status)) {
                        Swal.fire('Error', data.message || 'OTP verification failed', 'error');
                        otpBtn.disabled = false;
                        otpBtn.textContent = 'Verify OTP';
                        return;
                    }
                    // console.log('is verify value is = ',isverfiy)
                    if(isverfiy){

                    const fd = new FormData();
                    fd.append('email', pendingLoginEmail || userEmail);
                    fd.append('password', pendingLoginPassword);

                    const loginRes = await fetch("{{ route('admin.login') }}", {
                        method: "POST",
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .getAttribute('content'),
                            'Accept': 'application/json',
                        },
                        body: fd
                    });

                    const loginData = await loginRes.json();

                    if (loginRes.ok && loginData.status) {
                        Swal.fire('Success', 'Verified & Logged in successfully!', 'success')
                            .then(() => window.location.href = loginData.redirect);
                        return;
                    }

                    Swal.fire('Error', loginData.message || 'Login failed after verification', 'error');
                    }
                } catch (err) {
                    Swal.fire('Error', 'Server error. Try again later.', 'error');
                    console.error(err);
                }

                otpBtn.disabled = false;
                otpBtn.textContent = 'Verify OTP';
            });


            switchToSignup.addEventListener('click', (e) => {
                e.preventDefault();
                showSignup();
            });
            switchToLogin.addEventListener('click', (e) => {
                e.preventDefault();
                showLogin();
            });
            switchLogin.addEventListener('click', (e) => {
                e.preventDefault();
                showLogin();
            });

        });
    </script>

</body>

</html>
