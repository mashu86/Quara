<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - QUARA WALDROP Admin</title>
    <!-- Favicon / Shop Icon -->
    <link rel="icon" type="image/png" href="{{ asset('assets/images/favicon-round.png') }}?v=3">
    <link rel="shortcut icon" type="image/png" href="{{ asset('assets/images/favicon-round.png') }}?v=3">
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
            padding: 12px;
            border: none;
            transition: all 0.3s ease;
        }
        .btn-gold:hover {
            opacity: 0.95;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="login-card p-4 p-sm-5">
        <div class="text-center mb-4">
            <img src="{{ asset('assets/images/logo.png') }}" alt="QUARA WALDROP" style="max-height: 60px;">
            <h5 class="fw-bold mt-3 text-dark">Forgot Admin Password?</h5>
            <p class="text-muted small">Enter your email address and we'll send you a password reset link.</p>
        </div>

        @if(session('status'))
            <div class="alert alert-success rounded-3 small mb-3">
                {{ session('status') }}
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

        <form action="{{ route('admin.password.email') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="form-label small fw-bold text-uppercase">Admin Email Address</label>
                <input type="email" name="email" class="form-control rounded-3 bg-light" value="quarawaldrop@gmail.com" readonly required>
                <div class="form-text small text-muted"><i class="fa-solid fa-lock me-1"></i> Admin recovery emails are locked to the official address.</div>
            </div>

            <button type="submit" class="btn btn-gold w-100 shadow-sm mb-3">SEND RESET LINK</button>
            
            <div class="text-center">
                <a href="{{ route('admin.login') }}" class="small text-decoration-none text-muted">
                    <i class="fa-solid fa-arrow-left me-1"></i> Back to Login
                </a>
            </div>
        </form>
    </div>
</body>
</html>
