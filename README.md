# Sipresensi - Attendance Management System

Sipresensi adalah sistem manajemen absensi berbasis Laravel dengan teknologi pengenalan wajah dan validasi lokasi geografis. Sistem ini mendukung multiple user roles dan tracking absensi real-time dengan fitur check-in/check-out.

## ✨ Fitur Utama

- **Pengenalan Wajah**: Enrollment dan verifikasi absensi menggunakan teknologi face recognition
- **Validasi Lokasi**: Absensi berbasis lokasi dengan validasi radius GPS
- **Role-Based Access Control**: Super admin, admin, dan karyawan dengan hak akses berbeda
- **Laporan Komprehensif**: Dashboard pelaporan dan manajemen absensi lengkap
- **Real-time Tracking**: Tracking absensi real-time dengan fitur check-in/check-out
- **Responsive Design**: Interface yang mobile-friendly dengan dark mode support

## 🛠 Tech Stack

- **Backend**: PHP 8.2.27, Laravel 12.24.0
- **Frontend**: Livewire 3.6.4, Volt 1.7.2, Flux UI 2.2.4
- **Styling**: Tailwind CSS 4.0.7
- **Database**: SQLite (configurable)
- **Testing**: Pest 3.8.2
- **Code Quality**: Laravel Pint 1.24.0

## 📋 Persyaratan Sistem

- PHP >= 8.2
- Composer
- Node.js & NPM
- SQLite atau database lainnya yang didukung Laravel

## 🚀 Instalasi

### 1. Clone Repository

```bash
git clone <repository-url>
cd sipresensi
```

### 2. Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

### 3. Konfigurasi Environment

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Konfigurasi Database

```bash
# Jalankan migrasi dan seeder
php artisan migrate:fresh --seed
```

### 5. Konfigurasi Biznet Face API

Tambahkan konfigurasi Face API di file `.env`:

```env
BIZNET_FACE_API_TOKEN=tok_3zjeFwGN3Bqf1GrePCpHnEuc4s5zKyuf3RH7
BIZNET_FACE_API_URL=https://api.biznet.co.id/face
BIZNET_FACE_GALLERY_ID=sipresensi_production
```

### 6. Konfigurasi Admin Contact (Opsional)

```env
ADMIN_CONTACT_NAME="Administrator"
ADMIN_CONTACT_EMAIL="admin@company.com"
ADMIN_CONTACT_PHONE="+62 123-456-7890"
ADMIN_CONTACT_WHATSAPP="+62 123-456-7890"
ADMIN_CONTACT_DEPARTMENT="IT Support / HR"
```

## 🏃‍♂️ Menjalankan Aplikasi

### Development Mode

```bash
# Start all services (PHP server, queue worker, Vite)
composer run dev

# Atau jalankan secara terpisah:
php artisan serve          # Laravel development server
npm run dev                # Vite development server
php artisan queue:work     # Queue worker
```

### Production Build

```bash
npm run build
```

## 👥 Default Users

Sistem dilengkapi dengan default users untuk testing:

| Role | Username | Password |
|------|----------|----------|
| Admin | admin    | password |
| Karyawan | karyawan | password |

## 📊 Database Schema

### Model Utama

- **User**: Autentikasi dengan username-based login, roles via Spatie/Permission
- **Attendance**: Pengaturan absensi user (jadwal, lokasi, hari kerja)
- **AttendanceRecord**: Record individu check-in/check-out
- **Location**: Lokasi geografis dengan validasi radius
- **FaceEnrollment**: Penyimpanan data pengenalan wajah

### Relasi Database

- User hasOne Attendance (settings)
- User hasMany AttendanceRecords (daily records)
- User hasOne FaceEnrollment
- Attendance belongsTo Location
- AttendanceRecord belongsTo User, Location

## 🔐 Sistem Role & Permission

### Roles

- **Admin**: Manajemen user, manajemen absensi (tidak dapat mengelola super admin)
- **Karyawan**: Enrollment wajah, check-in/out absensi

### Autentikasi

Sistem menggunakan autentikasi berbasis username (bukan email).

## 🧪 Testing

### Menjalankan Tests

```bash
# Jalankan semua test
php artisan test

# Jalankan test file tertentu
php artisan test tests/Feature/ExampleTest.php

# Jalankan test dengan filter
php artisan test --filter=testName
```

### Test Structure

- Feature tests: `tests/Feature/` (primary)
- Unit tests: `tests/Unit/` (minimal)
- Framework: Pest testing framework

## 📱 Frontend Architecture

### Component Structure

- Mix dari Livewire Class-based components (`app/Livewire/`) dan Volt components (`resources/views/livewire/`)
- Flux UI components untuk konsistensi UI
- Responsive design dengan mobile-first approach
- Dark mode support

### Key Livewire Components

- `Administrator\ManageUsers` - CRUD user dengan role management
- `Administrator\ManageLocations` - Manajemen lokasi
- `Administrator\ManageAttendances` - Manajemen pengaturan absensi
- `Administrator\AttendanceReports` - Dashboard pelaporan

## 🗺 Fitur Lokasi & Geografis

### Validasi Lokasi

- Penyimpanan koordinat GPS (latitude/longitude)
- Validasi radius yang dapat dikonfigurasi (meter)
- Integrasi Leaflet.js untuk tampilan peta
- Verifikasi lokasi real-time saat check-in/out

## 💻 Development Commands

### Build & Development

```bash
composer run dev           # Start all services
npm run dev               # Start Vite development server
npm run build             # Build frontend assets
php artisan serve         # Start Laravel development server
```

### Code Quality

```bash
vendor/bin/pint --dirty   # Format code (run before committing)
```

### Database

```bash
php artisan migrate       # Run migrations
php artisan db:seed       # Seed database
php artisan migrate:fresh --seed  # Fresh migration with seeding
```

## 📂 Struktur Project

```
sipresensi/
├── app/
│   ├── Livewire/           # Livewire class-based components
│   └── Models/             # Eloquent models
├── database/
│   ├── migrations/         # Database migrations
│   └── seeders/           # Database seeders
├── resources/
│   └── views/
│       └── livewire/      # Volt components
├── routes/                # Route definitions
└── tests/                 # Test files
    ├── Feature/          # Feature tests
    └── Unit/             # Unit tests
```

## 🤝 Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Run tests (`php artisan test`)
4. Format code (`vendor/bin/pint --dirty`)
5. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
6. Push to the branch (`git push origin feature/AmazingFeature`)
7. Open a Pull Request

## 📄 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## 🆘 Support

Jika Anda mengalami masalah atau memiliki pertanyaan, silakan:

1. Check dokumentasi di atas
2. Lihat issue yang sudah ada di repository
3. Buat issue baru jika diperlukan
4. Hubungi admin system melalui kontak yang tersedia dalam aplikasi

## 🔧 Troubleshooting

### Common Issues

1. **Vite Error**: Jika mendapat error "Unable to locate file in Vite manifest", jalankan:
   ```bash
   npm run build
   # atau
   npm run dev
   ```

2. **Queue Jobs**: Pastikan queue worker berjalan untuk proses background:
   ```bash
   php artisan queue:work
   ```

3. **Face API**: Pastikan konfigurasi Biznet Face API sudah benar di file `.env`

4. **Location Permission**: Pastikan browser mengizinkan akses lokasi untuk fitur GPS
