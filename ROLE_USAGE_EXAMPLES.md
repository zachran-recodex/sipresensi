# Role Usage Examples

## Spatie Laravel Permission Implementation

Project ini telah berhasil mengimplementasikan `spatie/laravel-permission` dengan 3 role:
- **super admin** - Role tertinggi
- **admin** - Role administrator
- **karyawan** - Role karyawan/user biasa

## Cara Penggunaan

### 1. Mengecek Role User

```php
// Di Controller atau Blade
$user = auth()->user();

// Cek apakah user memiliki role tertentu
if ($user->hasRole('super admin')) {
    // User adalah super admin
}

if ($user->hasRole('admin')) {
    // User adalah admin
}

if ($user->hasRole('karyawan')) {
    // User adalah karyawan
}

// Cek multiple roles
if ($user->hasAnyRole(['admin', 'super admin'])) {
    // User adalah admin atau super admin
}
```

### 2. Menggunakan Middleware di Routes

```php
// Di routes/web.php
Route::middleware(['auth', 'role:super admin'])->group(function () {
    // Routes hanya untuk super admin
    Route::get('/admin/users', [UserController::class, 'index']);
});

Route::middleware(['auth', 'role:admin|super admin'])->group(function () {
    // Routes untuk admin atau super admin
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard']);
});

Route::middleware(['auth', 'role:karyawan'])->group(function () {
    // Routes untuk karyawan
    Route::get('/employee/profile', [EmployeeController::class, 'profile']);
});
```

### 3. Menggunakan di Blade Templates

```blade
<!-- Tampilkan konten berdasarkan role -->
@role('super admin')
    <div class="super-admin-panel">
        <h2>Super Admin Panel</h2>
        <!-- Konten khusus super admin -->
    </div>
@endrole

@role('admin')
    <div class="admin-panel">
        <h2>Admin Panel</h2>
        <!-- Konten khusus admin -->
    </div>
@endrole

@role('karyawan')
    <div class="employee-panel">
        <h2>Employee Dashboard</h2>
        <!-- Konten khusus karyawan -->
    </div>
@endrole

<!-- Multiple roles -->
@hasanyrole('admin|super admin')
    <div class="management-panel">
        <h2>Management Area</h2>
        <!-- Konten untuk admin atau super admin -->
    </div>
@endhasanyrole
```

### 4. Assign Role ke User

```php
// Di Controller atau Seeder
$user = User::find(1);

// Assign single role
$user->assignRole('admin');

// Assign multiple roles
$user->assignRole(['admin', 'karyawan']);

// Remove role
$user->removeRole('admin');

// Sync roles (replace all current roles)
$user->syncRoles(['karyawan']);
```

### 5. Menggunakan Factory dengan Role

```php
// Di Test atau Seeder
$superAdmin = User::factory()->superAdmin()->create([
    'name' => 'Super Administrator',
    'email' => 'superadmin@company.com'
]);

$admin = User::factory()->admin()->create([
    'name' => 'Administrator',
    'email' => 'admin@company.com'
]);

$karyawan = User::factory()->karyawan()->create([
    'name' => 'Employee User',
    'email' => 'karyawan@company.com'
]);
```

### 6. Policy dengan Role

```php
// Di Policy class
public function viewAny(User $user): bool
{
    return $user->hasAnyRole(['admin', 'super admin']);
}

public function create(User $user): bool
{
    return $user->hasRole('super admin');
}

public function update(User $user, Model $model): bool
{
    if ($user->hasRole('super admin')) {
        return true;
    }
    
    if ($user->hasRole('admin')) {
        // Admin dapat update dengan batasan tertentu
        return $this->adminCanUpdate($user, $model);
    }
    
    return false;
}
```

## Test Users yang Sudah Dibuat

Setelah menjalankan `php artisan db:seed`, user berikut telah dibuat:

1. **Super Admin**
   - Email: `superadmin@example.com`
   - Password: `password`
   - Role: `super admin`

2. **Admin User**
   - Email: `admin@example.com`
   - Password: `password`
   - Role: `admin`

3. **Karyawan User**
   - Email: `karyawan@example.com`
   - Password: `password`
   - Role: `karyawan`

## Middleware yang Tersedia

- `role:role_name` - Cek role spesifik
- `role:role1|role2` - Cek multiple roles (OR)
- `permission:permission_name` - Cek permission spesifik (jika nanti ditambahkan)
- `role_or_permission:role|permission` - Cek role atau permission

## Penggunaan di Controller

```php
public function index()
{
    $user = auth()->user();
    
    if ($user->hasRole('super admin')) {
        // Logic untuk super admin
        return view('admin.super-dashboard');
    } elseif ($user->hasRole('admin')) {
        // Logic untuk admin
        return view('admin.dashboard');
    } else {
        // Logic untuk karyawan
        return view('employee.dashboard');
    }
}
```

Implementasi ini memberikan sistem role yang fleksibel dan mudah digunakan untuk aplikasi sipresensi.