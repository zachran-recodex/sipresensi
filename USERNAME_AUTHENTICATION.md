# Username-Based Authentication Implementation

## Overview

Project sipresensi telah berhasil diubah dari sistem login berbasis email menjadi sistem login berbasis username. Perubahan ini mencakup semua aspek authentication, dari database hingga UI components.

## Changes Made

### 1. Database Changes
- ✅ **Migration Updated**: Kolom `username` ditambahkan ke tabel `users` dengan constraint `unique()`
- ✅ **Database Seeded**: Test users dibuat dengan username yang mudah diingat

### 2. Model Changes
- ✅ **User Model**: 
  - Ditambahkan `username` ke `$fillable` array
  - Override `getAuthIdentifierName()` untuk menggunakan `username`
  - Added `findForPassport()` method untuk compatibility

### 3. Factory & Seeder Updates
- ✅ **UserFactory**: Generate username otomatis menggunakan `fake()->unique()->userName()`
- ✅ **DatabaseSeeder**: Test users dibuat dengan username yang spesifik:
  - `superadmin` - Super Admin User
  - `admin` - Admin User  
  - `karyawan` - Karyawan User

### 4. Authentication Components
- ✅ **Login Component**: 
  - Property `$email` diganti dengan `$username`
  - Validation rules diperbarui
  - Auth attempt menggunakan username
  - Error messages dan throttling menggunakan username

- ✅ **Register Component**:
  - Ditambahkan field `username` dengan validation
  - Form UI diperbarui dengan input username

### 5. Form Validation
- ✅ **Login Validation**: `required|string` untuk username
- ✅ **Register Validation**: `required|string|max:255|unique:users` untuk username
- ✅ **UI Labels**: Semua label dan placeholder diperbarui

### 6. Testing
- ✅ **Role Tests**: Semua test role dan permission tetap berfungsi
- ✅ **Authentication Tests**: Test baru untuk username-based login
- ✅ **Factory Tests**: Test untuk username uniqueness dan creation
- ✅ **Livewire Tests**: Test untuk register dan login components

## Test Users

Setelah menjalankan `php artisan migrate:fresh --seed`, users berikut tersedia:

| Username | Email | Password | Role |
|----------|-------|----------|------|
| `superadmin` | superadmin@example.com | `password` | super admin |
| `admin` | admin@example.com | `password` | admin |
| `karyawan` | karyawan@example.com | `password` | karyawan |

## Usage Examples

### Login
```php
// Sekarang user login dengan username
Auth::attempt([
    'username' => 'admin', 
    'password' => 'password'
]);
```

### Creating Users
```php
// Via Factory
$user = User::factory()->create([
    'username' => 'newuser'
]);

// Via Factory dengan Role
$admin = User::factory()->admin()->create([
    'username' => 'newadmin'
]);

// Manual Creation
$user = User::create([
    'name' => 'John Doe',
    'username' => 'johndoe',
    'email' => 'john@example.com',
    'password' => Hash::make('password')
]);
```

### Validation Rules
```php
// Login Validation
'username' => ['required', 'string'],
'password' => ['required', 'string']

// Register Validation  
'username' => ['required', 'string', 'max:255', 'unique:users'],
'name' => ['required', 'string', 'max:255'],
'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users'],
'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()]
```

### Frontend Forms
```blade
<!-- Login Form -->
<flux:input
    wire:model="username"
    :label="__('Username')"
    type="text"
    required
    autofocus
    autocomplete="username"
    placeholder="username"
/>

<!-- Register Form -->  
<flux:input
    wire:model="username"
    :label="__('Username')"
    type="text"
    required
    autocomplete="username"
    placeholder="username"
/>
```

## Authentication Identifier

User model sekarang menggunakan username sebagai authentication identifier:

```php
public function getAuthIdentifierName(): string
{
    return 'username';
}
```

## Backward Compatibility

⚠️ **Breaking Changes**: 
- Email-based login tidak lagi berfungsi
- Semua authentication sekarang harus menggunakan username
- API dan frontend perlu diperbarui untuk menggunakan field `username`

## Testing

Semua test telah diperbarui dan passing:
- ✅ 19 tests passed (58 assertions)
- ✅ Role-based authentication tetap berfungsi
- ✅ Username validation dan uniqueness
- ✅ Livewire components compatibility

## Next Steps

1. **Frontend Integration**: Update semua frontend code yang masih menggunakan email
2. **API Updates**: Jika ada API endpoints, update untuk menggunakan username
3. **Documentation**: Update user documentation dan training materials
4. **Password Reset**: Pertimbangkan apakah password reset masih menggunakan email atau perlu update

## Migration Commands

Untuk menerapkan perubahan ini pada environment lain:

```bash
# Fresh migration dengan seed
php artisan migrate:fresh --seed

# Atau hanya run migration baru
php artisan migrate

# Seed roles dan users
php artisan db:seed --class=RoleSeeder
php artisan db:seed --class=DatabaseSeeder
```