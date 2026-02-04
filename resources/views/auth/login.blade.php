<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login Absensi Eskul</title>

    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/plugins.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/kaiadmin.min.css') }}">
    <style>
.wrapper-login {
    min-height: 100vh;
    display: flex;
    justify-content: center;
}

/* Default desktop = tengah */
@media (min-width: 769px) {
    .wrapper-login {
        align-items: center;
    }
}

@media (max-width: 768px) and (max-height: 720px) {
    .wrapper-login {
        align-items: flex-start;
        padding-top: 30px;
    }
}

@media (max-width: 768px) and (min-height: 721px) {
    .wrapper-login {
        align-items: center;
        padding-top: 0;
    }
}

.container-login h3 {
    margin-bottom: 20px;
}
</style>

    
</head>

<body class="login">

<div class="wrapper wrapper-login">
<div class="container container-login animated fadeIn">

    <h3 class="text-center mb-3 p-5">Login Absensi Eskul</h3>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="form-group">
            <label>Email / Username / NISN</label>
            <input type="text" name="login" class="form-control" required autofocus>
        </div>

        <div class="form-group mt-3">
            <label>Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <div class="form-check mt-2">
            <input type="checkbox" name="remember" class="form-check-input">
            <label class="form-check-label">Remember Me</label>
        </div>

        <button class="btn btn-primary w-100 mt-3 mb-2 p-10">
            Login
        </button>

    </form>

</div>
</div>

<script src="{{ asset('assets/js/core/jquery-3.7.1.min.js') }}"></script>
<script src="{{ asset('assets/js/core/bootstrap.min.js') }}"></script>
<script src="{{ asset('assets/js/kaiadmin.min.js') }}"></script>

</body>
</html>
x