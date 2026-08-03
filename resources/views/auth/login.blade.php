<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Batam Pos</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
    body {
        background-color: #ffffff;
        font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
    }

    .login-card {
        background-color: #f8f7f2;
        border: 1px solid #d9d9d4;
        border-radius: 16px;
        max-width: 400px;
        width: 100%;
    }

    .login-card .form-control {
        background-color: #ffffff;
        border: 1px solid #d9d9d4;
        border-radius: 8px;
        padding: 10px 12px;
        font-size: 14px;
    }

    .login-card .form-control:focus {
        border-color: #b8b8b3;
        box-shadow: none;
    }

    .login-card .form-label {
        font-size: 13px;
        font-weight: 500;
        color: #1a1a1a;
        margin-bottom: 6px;
    }

    .btn-login {
        background-color: #eceae3;
        border: 1px solid #1a1a1a;
        color: #1a1a1a;
        font-weight: 600;
        border-radius: 8px;
        padding: 10px 16px;
        font-size: 14px;
        transition: background-color 0.2s;
    }

    .btn-login:hover {
        background-color: #e2e0d8;
        color: #1a1a1a;
        border-color: #1a1a1a;
    }

    .login-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1a1a1a;
    }

    .login-subtitle {
        font-size: 14px;
        color: #4a4a4a;
    }

    .login-footer {
        font-size: 12px;
        color: #4a4a4a;
        line-height: 1.5;
    }

    .input-group .btn {
    border: 1px solid #d9d9d4;
    border-left: none;
    background-color: #ffffff;
}

.input-group .form-control {
    border-right: 0;
}

.input-group .btn {
    background: #fff;
    border: 1px solid #d9d9d4;
    border-left: 0;
}

.input-group .btn i {
    font-size: 1rem;
    color: #6c757d;
}

.input-group .btn:hover i {
    color: #000;
}

.input-group .btn:focus {
    box-shadow: none;
}
    </style>
</head>

<body>

    <div class="d-flex vh-100 align-items-center justify-content-center px-3">
        <div class="login-card p-4 p-md-5 shadow-sm">

            <!-- Header -->
            <div class="text-center mb-4">
                <h1 class="login-title mb-1">Batam Pos</h1>
                <p class="login-subtitle mb-0">Sistem Peminjaman Aset</p>
            </div>

            @if ($errors->any())
            <div class="alert alert-danger py-2 px-3 mb-3" style="font-size:13px;">
                {{ $errors->first() }}
            </div>
            @endif

            <!-- Form -->
            <form action="{{ route('login') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email"
                         placeholder="budi@batampos.co.id" required>
                </div>

                <div class="mb-4">
    <label for="password" class="form-label">Password</label>

    <div class="input-group">
        <input
            type="password"
            class="form-control"
            id="password"
            name="password"
            placeholder="••••••••"
            required>

        <button
            class="btn btn-outline-secondary"
            type="button"
            id="togglePassword">

            <i class="bi bi-eye"></i>
        </button>
    </div>
</div>

                <button type="submit"
                    class="btn btn-login w-100 d-flex align-items-center justify-content-center gap-2">
                    Masuk ke sistem
                    <i class="bi bi-arrow-up-right"></i>
                </button>

            </form>

            <!-- Footer -->
            <div class="login-footer text-center mt-4">
                Sistem mendeteksi peran otomatis<br>
                (Karyawan / HR / Admin)
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

    <script>
document.addEventListener('DOMContentLoaded', function () {

    const password = document.getElementById('password');
    const togglePassword = document.getElementById('togglePassword');
    const icon = togglePassword.querySelector('i');

    togglePassword.addEventListener('click', function () {

        if (password.type === 'password') {
            password.type = 'text';
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        } else {
            password.type = 'password';
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        }

    });

});
</script>

</body>

</html>