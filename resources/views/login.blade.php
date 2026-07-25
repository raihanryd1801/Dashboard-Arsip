<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Inter', sans-serif; 
            background-color: #e2e8f0; /* Latar abu-abu terang */
            height: 100vh; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            margin: 0;
        }
        .login-wrapper {
            width: 100%;
            max-width: 420px;
            padding: 15px;
        }
        .login-card { 
            border: none; 
            border-radius: 16px; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.1); 
            overflow: hidden;
            background: #fff;
        }
        .login-header {
    background: #0f172a;
    min-height: 180px;

    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;

    padding: 20px;
    text-align: center;
    color: #fff;
}

.login-logo {
    width: 250px;
    max-width: 100%;
    height: auto;
    display: block;
    margin: 0 auto 15px;
}
        .form-control {
            border-radius: 8px;
            padding: 12px 15px;
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
        }
        .form-control:focus {
            box-shadow: none;
            border-color: #3b82f6;
            background-color: #fff;
        }
        .btn-login {
            background: #3b82f6;
            border: none;
            border-radius: 8px;
            padding: 12px;
            transition: all 0.3s;
        }
        .btn-login:hover {
            background: #2563eb;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>

<div class="login-wrapper">
    <div class="card login-card">
        <!-- HEADER LOGIN -->
        <div class="login-header">
            <!-- INI KODE UNTUK MEMANGGIL FANS.PNG -->
            <img src="{{ asset('skykom.webp') }}" alt="Logo" class="login-logo">
            
            <h4 class="mb-1 fw-bold tracking-wide">PORTAL ARSIP DOKUMEN MULTI COMPANY</h4>
            <p class="mb-0 text-white-50 small">CV Darma Bakti Mandiri</p>
        </div>
        
        <!-- BODY FORM LOGIN -->
        <div class="card-body p-4 p-md-5">
            <!-- Notifikasi Error jika salah password -->
            @if(session('error'))
                <div class="alert alert-danger small p-2 text-center rounded-3">
                    {{ session('error') }}
                </div>
            @endif
            
            <form action="/login" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label text-muted small fw-bold">Email / Username</label>
                    <input type="text" name="email" class="form-control" placeholder="Masukkan akses Anda..." required autofocus>
                </div>
                <div class="mb-4">
                    <label class="form-label text-muted small fw-bold">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>
                <button type="submit" class="btn btn-login w-100 text-white fw-bold">MASUK SISTEM</button>
            </form>
        </div>
    </div>
    
    <div class="text-center mt-4">
        <small class="text-muted">&reg {{ date('Y') }} Github@raihanryd01</small>
    </div>
</div>

</body>
</html>