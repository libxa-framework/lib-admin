# LibAdmin - Admin Panel Package for LibxaFrame

A powerful, modular admin panel package for LibxaFrame framework that provides authentication, resource management, and a beautiful admin interface out of the box.

## Features

- 🔐 **Authentication System** - Session-based authentication with login/logout
- 👥 **User Management** - Create, read, update, and delete admin users
- 📊 **Resource Management** - Dynamic CRUD operations for any database table
- 🎨 **Beautiful UI** - Modern, responsive admin interface with shared layout
- 🔧 **Console Commands** - CLI tools for managing admin users and resources
- 📦 **Modular Architecture** - Fully integrated with LibxaFrame package system
- 🛡️ **Middleware Protection** - Route protection for authenticated and guest users
- 🔒 **Password Hashing** - Automatic bcrypt hashing for user passwords

## Installation

LibAdmin is already integrated into your LibxaFrame application. No additional installation required.

## Quick Start

### 1. Run Migrations

```bash
php libxa migrate
```

This creates:
- `admin_users` - Admin user accounts
- `roles` - User roles
- `permissions` - Permission definitions
- `role_user` - Role assignments
- `permission_role` - Permission assignments
- `audit_logs` - Activity tracking
- `media` - File management

### 2. Create an Admin User

```bash
php libxa admin:make-user
```

Follow the prompts to enter:
- Name
- Email
- Password
- Role (default: superadmin)

Or use command-line arguments:

```bash
php libxa admin:make-user "John Doe" "john@example.com" "password123" --role=admin
```

### 3. Access the Admin Panel

Navigate to `/admin/login` in your browser and login with the credentials you created.

## Authentication

### Components

- **AdminUser Model** (`Libxa\Admin\Models\AdminUser`) - User model with password hashing
- **AdminGuard** (`Libxa\Admin\Auth\AdminGuard`) - Session-based authentication guard
- **AdminUserProvider** (`Libxa\Admin\Auth\AdminUserProvider`) - User retrieval and validation
- **AdminAuthMiddleware** - Protects routes requiring authentication
- **RedirectIfAuthenticated** - Redirects authenticated users from login page

### Routes

```
GET  /admin/login    - Login form (guest only)
POST /admin/login    - Login submit
POST /admin/logout   - Logout (auth required)
```

## Console Commands

### admin:make-user

Create a new admin user.

```bash
php libxa admin:make-user
php libxa admin:make-user "Name" "email@example.com" "password" --role=admin
```

### admin:make-resource

Generate a resource class for managing database tables.

```bash
php libxa admin:make-resource User
php libxa admin:make-resource Product --fields=name,price,description
```

### admin:roles

List all available roles.

```bash
php libxa admin:roles
```

## Resource Management

### Accessing Resources

All resources follow the pattern: `/admin/resources/{resource}`

Examples:
- `/admin/resources/users` - Manage users
- `/admin/resources/products` - Manage products
- `/admin/resources/posts` - Manage blog posts

### CRUD Operations

#### List All Items
```
GET /admin/resources/{resource}
```
Displays a table with all items from the specified table.

#### Create New Item
```
GET /admin/resources/{resource}/create
POST /admin/resources/{resource}
```
Shows a form to create a new item. Features:
- Automatic column filtering (only inserts columns that exist)
- Automatic password hashing
- Validation

#### View Item Details
```
GET /admin/resources/{resource}/{id}
```
Displays all properties of a specific item with action buttons.

#### Edit Item
```
GET /admin/resources/{resource}/{id}/edit
PUT /admin/resources/{resource}/{id}
```
Pre-filled form to edit an item. Password field can be left blank to keep current value.

#### Delete Item
```
DELETE /admin/resources/{resource}/{id}
```
Deletes an item with confirmation prompt.

### Dynamic Table Handling

The ResourceController automatically adapts to any table structure:

```php
public function store(Request $request, string $resource): Response
{
    $data = $request->except(['_token', '_method']);
    
    // Get table columns to filter data
    $columns = DB::select("PRAGMA table_info($resource)");
    $validColumns = array_column($columns, 'name');
    
    // Only include columns that exist in the table
    $filteredData = array_intersect_key($data, array_flip($validColumns));
    
    // Hash password if present
    if (isset($filteredData['password'])) {
        $filteredData['password'] = password_hash($filteredData['password'], PASSWORD_BCRYPT);
    }
    
    DB::table($resource)->insert($filteredData);
    return redirect("/admin/resources/$resource");
}
```

This means you can manage ANY database table without configuration!

## Views and Layouts

### Shared Layout

All admin pages use `resources/views/layouts/admin.blade.php`:

- **Sidebar** - Navigation menu with links to Dashboard and resources
- **Header** - Page title, user welcome message, logout button
- **Content Area** - Page-specific content

### Available Views

1. **Dashboard** (`admin::dashboard`)
   - Welcome screen with navigation

2. **Login** (`admin::auth.login`)
   - Login form with remember me option
   - Error message display

3. **Resource Index** (`admin::resources.index`)
   - Table view of all items
   - Dynamic column headers based on table structure
   - Action buttons (View, Edit, Delete)
   - Create New button

4. **Resource Create** (`admin::resources.create`)
   - Form with name, email, password, role fields
   - Cancel button

5. **Resource Show** (`admin::resources.show`)
   - Item details in grid layout
   - Edit and Delete buttons
   - Back navigation

6. **Resource Edit** (`admin::resources.edit`)
   - Pre-filled form with existing data
   - Dynamic field generation based on table structure
   - Password field (optional - leave blank to keep current)

## Routes

### Web Routes

```php
// Public (guest only)
GET  /admin/login           - Login form
POST /admin/login           - Login submit

// Protected (auth required)
GET  /admin                 - Dashboard
POST /admin/logout          - Logout
GET  /admin/dashboard       - Dashboard
GET  /admin/resources/{resource}              - List items
GET  /admin/resources/{resource}/create       - Create form
POST /admin/resources/{resource}              - Store item
GET  /admin/resources/{resource}/{id}         - Show item
GET  /admin/resources/{resource}/{id}/edit    - Edit form
PUT  /admin/resources/{resource}/{id}         - Update item
DELETE /admin/resources/{resource}/{id}         - Delete item
GET  /admin/media           - Media management
POST /admin/media           - Media upload
DELETE /admin/media/{id}    - Media delete
GET  /admin/settings        - Settings
PUT  /admin/settings        - Update settings
```

### API Routes

```php
GET    /api/admin/resources/{resource}           - API index
POST   /api/admin/resources/{resource}           - API store
GET    /api/admin/resources/{resource}/{id}      - API show
PUT    /api/admin/resources/{resource}/{id}      - API update
DELETE /api/admin/resources/{resource}/{id}      - API delete
```

## Middleware

### AdminAuthMiddleware

Protects routes requiring authentication. Redirects unauthenticated users to `/admin/login`.

Usage:
```php
$router->group(['middleware' => \Libxa\Admin\Http\Middleware\AdminAuthMiddleware::class], function ($router) {
    $router->get('/dashboard', [DashboardController::class, 'index']);
});
```

### RedirectIfAuthenticated

Redirects authenticated users away from login page.

Usage:
```php
$router->group(['middleware' => \Libxa\Admin\Http\Middleware\RedirectIfAuthenticated::class], function ($router) {
    $router->get('/login', function () {
        return view('admin::auth.login');
    });
});
```

## Complete Example

Here's a complete example of using LibAdmin:

### Step 1: Setup

```bash
# Run migrations
php libxa migrate

# Create admin user
php libxa admin:make-user "Admin User" "admin@example.com" "securepass123"

# Start development server
php libxa serve
```

### Step 2: Login

Navigate to `http://127.0.0.1:8000/admin/login`
- Email: `admin@example.com`
- Password: `securepass123`

### Step 3: Manage Users

Navigate to `http://127.0.0.1:8000/admin/resources/users`

You can now:
- View all users in a table
- Create new users
- Edit existing users
- Delete users

### Step 4: Manage Any Table

The beauty of LibAdmin is that it works with ANY database table.

If you have a `posts` table, simply navigate to:
`http://127.0.0.1:8000/admin/resources/posts`

The interface automatically:
- Detects table columns
- Generates appropriate form fields
- Displays data in a table
- Handles CRUD operations

### Step 5: Create Resources (Optional)

For advanced customization, create resource classes:

```bash
php libxa admin:make-resource Product
```

This generates a resource class in your application that you can customize.

## Controllers

### AuthController

```php
class AuthController
{
    public function login(Request $request): Response
    {
        $credentials = $request->only(['email', 'password']);
        $remember = $request->input('remember') === 'on';

        if ($this->auth->attempt($credentials, $remember)) {
            return redirect('/admin/dashboard');
        }

        return redirect('/admin/login')->with('error', 'Invalid credentials');
    }

    public function logout(Request $request): Response
    {
        $this->auth->logout();
        return redirect('/admin/login');
    }
}
```

### DashboardController

```php
class DashboardController
{
    public function index(): Response
    {
        $user = $this->auth->user();
        return view('admin::dashboard', compact('user'));
    }
}
```

### ResourceController

Dynamic CRUD controller that works with any table:

```php
class ResourceController
{
    public function index(string $resource): Response
    {
        $items = DB::table($resource)->get();
        return view('admin::resources.index', compact('resource', 'items'));
    }

    public function store(Request $request, string $resource): Response
    {
        // Automatically filters columns and hashes passwords
        $filteredData = $this->filterData($resource, $request->all());
        DB::table($resource)->insert($filteredData);
        return redirect("/admin/resources/$resource");
    }
}
```

## Customization

### Customizing the Layout

Edit `packages/lib-admin/src/Resources/views/layouts/admin.blade.php` to customize:
- Sidebar navigation
- Header styling
- Color scheme
- Logo

### Customizing Forms

Edit the resource views in `packages/lib-admin/src/Resources/views/resources/`:
- `create.blade.php` - Customize create form
- `edit.blade.php` - Customize edit form
- `index.blade.php` - Customize table view
- `show.blade.php` - Customize detail view

## Security Features

- **Password Hashing** - Automatic bcrypt hashing for passwords
- **Session Management** - Secure session-based authentication
- **Route Protection** - Middleware guards for protected routes
- **Input Filtering** - Filters data to match table columns (prevents SQL errors)
- **CSRF Protection** - Framework-level CSRF protection

## Troubleshooting

### "No commands defined in 'admin' namespace"

Run package discovery:
```bash
php libxa package:discover
```

### "Table has no column named X"

The ResourceController automatically filters fields to match table columns. Extra form fields are ignored. This is normal behavior.

### Can't access admin panel

1. Ensure you've run migrations: `php libxa migrate`
2. Create an admin user: `php libxa admin:make-user`
3. Check that the server is running: `php libxa serve`
4. Navigate to `/admin/login` (not `/admin`)

## Package Structure

```
packages/lib-admin/
├── src/
│   ├── AdminServiceProvider.php      # Service provider
│   ├── Models/
│   │   └── AdminUser.php            # User model
│   ├── Auth/
│   │   ├── AdminGuard.php           # Authentication guard
│   │   └── AdminUserProvider.php    # User provider
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php
│   │   │   ├── DashboardController.php
│   │   │   └── ResourceController.php
│   │   └── Middleware/
│   │       ├── AdminAuthMiddleware.php
│   │       └── RedirectIfAuthenticated.php
│   ├── Console/Commands/
│   │   ├── MakeUserCommand.php
│   │   ├── MakeResourceCommand.php
│   │   └── RolesCommand.php
│   ├── Routes/
│   │   ├── web.php
│   │   └── api.php
│   ├── Resources/views/
│   │   ├── layouts/
│   │   │   └── admin.blade.php      # Shared layout
│   │   ├── auth/
│   │   │   └── login.blade.php
│   │   ├── dashboard.blade.php
│   │   └── resources/
│   │       ├── index.blade.php
│   │       ├── create.blade.php
│   │       ├── show.blade.php
│   │       └── edit.blade.php
│   └── Database/Migrations/         # Package migrations
└── composer.json
```

## License

Part of the LibxaFrame framework.
