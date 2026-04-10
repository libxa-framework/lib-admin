<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin - LibAdmin')</title>
</head>
<body>
    <div style="display: flex; height: 100vh;">
        <!-- Sidebar -->
        <div style="width: 250px; background: #1e293b; color: white; padding: 20px;">
            <h2 style="margin-bottom: 30px;">LibAdmin</h2>
            <ul style="list-style: none; padding: 0;">
                <li style="padding: 10px; background: #334155; border-radius: 4px; margin-bottom: 5px;">
                    <a href="/admin/dashboard" style="color: white; text-decoration: none;">Dashboard</a>
                </li>
                <li style="padding: 10px; margin-bottom: 5px;">
                    <a href="/admin/resources/users" style="color: #94a3b8; text-decoration: none;">Users</a>
                </li>
            </ul>
        </div>

        <!-- Main Content -->
        <div style="flex: 1; display: flex; flex-direction: column;">
            <!-- Header -->
            <div style="background: white; padding: 15px 30px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
                <h1 style="margin: 0;">@yield('header', 'Dashboard')</h1>
                <div>
                    @if(isset($user) && $user)
                        <span style="margin-right: 15px;">Welcome, {{ $user->name ?? 'Admin' }}</span>
                    @else
                        <span style="margin-right: 15px;">Welcome, Admin</span>
                    @endif
                    <form action="/admin/logout" method="POST" style="display: inline;">
                        <button type="submit" style="padding: 8px 16px; background: #ef4444; color: white; border: none; border-radius: 4px; cursor: pointer;">
                            Logout
                        </button>
                    </form>
                </div>
            </div>

            <!-- Content -->
            <div style="padding: 30px;">
                @yield('content')
            </div>
        </div>
    </div>
</body>
</html>
