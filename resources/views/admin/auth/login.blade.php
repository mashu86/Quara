<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - QUARA WALDROP</title>
    <!-- Favicon / Shop Icon -->
    <link rel="icon" type="image/png" href="{{ asset('assets/images/logo.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('assets/images/logo.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background: linear-gradient(135deg, #111111 0%, #222222 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: #FFFFFF;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4);
            width: 100%;
            max-width: 420px;
            overflow: hidden;
        }
        .btn-gold {
            background: linear-gradient(135deg, #C9962E 0%, #9A6A12 100%);
            color: #FFF;
            font-weight: 600;
            border-radius: 50px;
            padding: 10px 16px;
            font-size: 0.92rem;
            border: none;
            transition: all 0.3s ease;
        }
        .btn-gold:hover {
            opacity: 0.95;
            transform: translateY(-2px);
        }
        @media (max-width: 576px) {
            .btn-gold {
                padding: 7px 12px;
                font-size: 0.78rem;
            }
            .login-card {
                padding: 1.25rem !important;
                border-radius: 16px;
            }
        }
        .input-group-text {
            cursor: pointer;
            background-color: #F8F9FA;
        }
    </style>
</head>
<body>
    <div class="login-card p-4 p-sm-5">
        <div class="text-center mb-4">
            <img src="{{ asset('assets/images/logo.png') }}" alt="QUARA WALDROP" style="max-height: 60px;">
            <h5 class="fw-bold mt-3 text-dark">Admin Portal Login</h5>
            <p class="text-muted small">Manage catalog, orders & inventory</p>
        </div>

        @if(session('status'))
            <div class="alert alert-success rounded-3 small mb-3">
                {{ session('status') }}
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert-success rounded-3 small mb-3">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger rounded-3 small mb-3">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger rounded-3 small mb-3">
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.login.post') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label small fw-bold text-uppercase">Username or Email</label>
                <input type="text" name="login" class="form-control rounded-3" placeholder="Enter username/email" value="{{ old('login') }}" required autofocus>
            </div>

            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label class="form-label small fw-bold text-uppercase mb-0">Password</label>
                    <a href="{{ route('admin.password.request') }}" class="small text-decoration-none" style="color: #C9962E;">Forgot Password?</a>
                </div>
                <div class="input-group">
                    <input type="password" name="password" id="loginPassword" class="form-control rounded-start-3" placeholder="••••••••" required>
                    <span class="input-group-text rounded-end-3" id="togglePasswordBtn">
                        <i class="fa-solid fa-eye" id="togglePasswordIcon"></i>
                    </span>
                </div>
            </div>

            <div class="mb-4 form-check">
                <input type="checkbox" name="remember" class="form-check-input" id="remember">
                <label class="form-check-label small" for="remember">Remember me</label>
            </div>

            <button type="submit" class="btn btn-gold w-100 shadow-sm">LOG IN TO DASHBOARD</button>
        </form>
    </div>

    <script>
        document.getElementById('togglePasswordBtn').addEventListener('click', function() {
            const passwordInput = document.getElementById('loginPassword');
            const icon = document.getElementById('togglePasswordIcon');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    </script>
</body>
</html>
