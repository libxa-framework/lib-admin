<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - LibAdmin</title>
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-card">
            <div class="brand">
                <h1>LibAdmin</h1>
            </div>
            @if(session('error'))
                <div style="background: #fee; color: #c33; padding: 10px; border-radius: 4px; margin-bottom: 20px;">
                    {{ session('error') }}
                </div>
            @endif
            <form method="POST" action="/admin/login">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required autofocus>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="remember"> Remember me
                    </label>
                </div>
                <button type="submit">Login</button>
            </form>
        </div>
    </div>
</body>
</html>
