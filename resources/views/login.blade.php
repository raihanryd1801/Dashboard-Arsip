<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login - Arsip Unit</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-secondary d-flex align-items-center justify-content-center" style="height: 100vh;">

<div class="card shadow-lg p-4" style="width: 100%; max-width: 400px;">
    <h3 class="text-center mb-4">Portal Arsip Unit</h3>
    
    @if($errors->any())
        <div class="alert alert-danger p-2 text-center">{{ $errors->first() }}</div>
    @endif

    <form action="/login" method="POST">
        @csrf
        <div class="mb-3">
            <label>Email Akses</label>
            <input type="email" name="email" class="form-control" placeholder="admin@dankom.co.id" required>
        </div>
        <div class="mb-4">
            <label>Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Login Dashboard</button>
    </form>
</div>

</body>
</html>