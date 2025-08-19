# Tutorial Cara Menggunakan Layanan AI/ML (Face Recognition)

## A. Pendahuluan

Layanan AI/ML Face Recognition dapat Anda gunakan untuk mengembangkan layanan berbasis pengenalan wajah. Untuk pengenalan dan cara pemesanannya, Anda dapat membaca artikel berikut terlebih dahulu: **Cara Berlangganan Layanan AI/ML (Face Recognition)**.

Di artikel ini, kita akan mempelajari cara menggunakan layanan Face Recognition. Sebelum lanjut ke cara penggunaan API, penting untuk memahami topologi Face Recognition yang menggambarkan bagaimana sistem Face Recognition bekerja.

### Komponen Sistem Face Recognition

Berikut penjelasan untuk masing-masing komponen yang terlibat dalam sistem:

- **Client**: Admin atau program (aplikasi) Anda yang mengakses API, serta pihak yang memiliki FaceGallery
- **FaceGallery**: Kumpulan (biasanya dalam bentuk tangkapan foto/gambar) pengguna dari suatu tempat atau area. Anggap saja seperti "database"
- **User**: Orang yang menggunakan API untuk pengenalan wajah dan datanya disimpan pada database

---

## B. Konfigurasi

### 1. Demo dengan Postman

Selanjutnya kita akan mencoba menggunakan produk tersebut melalui Postman sebagai software uji coba.

#### Instalasi Postman

Apabila Anda belum menginstall Postman, silakan unduh terlebih dahulu pada tautan berikut: [https://www.postman.com/downloads](https://www.postman.com/downloads)

#### Mendapatkan Token Autentikasi

Untuk mengakses API, Anda membutuhkan token sebagai autentikasi. Token dapat dilihat pada portal [https://portal.biznetgio.com](https://portal.biznetgio.com) dengan langkah berikut:

1. Login ke portal Biznet Gio
2. Pilih menu **AI and ML** > **Face Recognition**
3. Klik nama service yang telah dibuat
4. Salin dan simpan Token yang ditampilkan

> **Catatan**: Token ini akan digunakan pada setiap pemanggilan API

#### Menggunakan Get Counters Endpoint

Endpoint ini digunakan untuk melihat sisa kuota pemakaian API.

**Endpoint URL:**
```
GET https://fr.neoapi.id/risetai/face-api/client/get-counters
```

**Headers:**
```
Key: Accesstoken
Value: [ISI-TOKEN-YANG-DI-COPY-SEBELUMNYA]
```

**Request Body (JSON):**
```json
{
    "trx_id": "unique_alphanumeric_string_123"
}
```

**Parameter:**

| Key | Type | Description |
|-----|------|-------------|
| trx_id | String | Digunakan untuk keperluan logging dan debugging, isi dengan unique string alphanumeric |

**Response Berhasil:**
```json
{
    "status": "200",
    "status_message": "Success",
    "remaining_limit": {
        "n_api_hits": 1000,
        "n_face": 500,
        "n_facegallery": 10
    }
}
```

> **Catatan**: `n_api_hits` menunjukkan sisa jumlah pemanggilan API yang dapat dilakukan

**Response Gagal (Token tidak valid):**
```json
{
    "status": "401",
    "status_message": "Access token not authorized"
}
```

---

### 2. Mengelola FaceGallery

#### 2.1 GET My Facegalleries

Endpoint API ini memberikan daftar FaceGalleries yang Anda miliki.

**Endpoint URL:**
```
GET https://fr.neoapi.id/risetai/face-api/facegallery/my-facegalleries
```

**Headers:**
```
Accesstoken: [TOKEN_ID]
```

**Request Body:** Tidak diperlukan

**Response:**
```json
{
    "status": "200",
    "status_message": "Success",
    "facegallery_id": [
        "perusahaan_A",
        "departemen_IT",
        "kantor_jakarta"
    ]
}
```

#### 2.2 POST Create Facegallery

Endpoint ini digunakan untuk membuat FaceGallery baru seperti nama lokasi, perusahaan, atau departemen.

**Endpoint URL:**
```
POST https://fr.neoapi.id/risetai/face-api/facegallery/create-facegallery
```

**Headers:**
```
Accesstoken: [TOKEN_ID]
```

**Request Body (JSON):**
```json
{
    "facegallery_id": "nama_facegallery_baru",
    "trx_id": "unique_string_123"
}
```

**Parameter:**

| Key | Type | Description |
|-----|------|-------------|
| facegallery_id | String | Nama FaceGallery yang ingin dibuat |
| trx_id | String | Unique string alphanumeric |

**Response:**
```json
{
    "status": "200",
    "status_message": "Success",
    "facegallery_id": "nama_facegallery_baru"
}
```

#### 2.3 DELETE Delete Facegallery

Endpoint ini digunakan untuk menghapus FaceGallery.

**Endpoint URL:**
```
DELETE https://fr.neoapi.id/risetai/face-api/facegallery/delete-facegallery
```

**Headers:**
```
Accesstoken: [TOKEN_ID]
```

**Request Body (JSON):**
```json
{
    "facegallery_id": "nama_facegallery_yang_akan_dihapus",
    "trx_id": "unique_string_123"
}
```

**Parameter:**
Parameter sama dengan saat membuat FaceGallery, tetapi menggunakan method DELETE.

---

### 3. Mengelola User (Face Enrollment)

#### 3.1 POST Enroll Face

Endpoint ini digunakan untuk menambahkan atau registrasi user ke database.

**Endpoint URL:**
```
POST https://fr.neoapi.id/risetai/face-api/facegallery/enroll-face
```

**Headers:**
```
Accesstoken: [TOKEN_ID]
```

**Request Body (JSON):**
```json
{
    "user_id": "NIK123456789",
    "user_name": "John Doe",
    "facegallery_id": "perusahaan_A",
    "image": "data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQ...",
    "trx_id": "unique_string_123"
}
```

**Parameter:**

| Key | Type | Description |
|-----|------|-------------|
| user_id | String | Unique user identifier (NIK, NIM, ID Karyawan, atau email) |
| user_name | String | Nama user yang didaftarkan |
| facegallery_id | String | Nama facegallery yang sudah dibuat sebelumnya |
| image | String | Base64 encoded JPG atau PNG image |
| trx_id | String | Unique string alphanumeric |

#### Cara Encoding Image ke Base64

Untuk testing dengan Postman, Anda dapat menggunakan website third-party untuk convert image ke Base64:

**Website Converter:**
- [https://base64.guru/converter/encode/image](https://base64.guru/converter/encode/image)
- [https://www.base64encode.org/](https://www.base64encode.org/)

**Langkah-langkah:**
1. Upload image wajah ke website converter
2. Copy hasil string Base64 yang dihasilkan
3. Paste string Base64 tersebut ke parameter `image` di request body

> **Catatan**: Pada implementasi nyata, Anda dapat menggunakan webcam atau camera device untuk mengcapture wajah dan melakukan encode ke Base64 secara langsung.

**Response Berhasil:**
```json
{
    "status": "200",
    "status_message": "Success"
}
```

#### 3.2 GET List Faces

Endpoint ini digunakan untuk melihat daftar wajah yang sudah ditambahkan sebelumnya.

**Endpoint URL:**
```
GET https://fr.neoapi.id/risetai/face-api/facegallery/list-faces
```

**Headers:**
```
Accesstoken: [TOKEN_ID]
```

**Request Body (JSON):**
```json
{
    "facegallery_id": "perusahaan_A",
    "trx_id": "unique_string_123"
}
```

**Parameter:**
| Key | Type | Description |
|-----|------|-------------|
| facegallery_id | String | Nama facegallery yang ingin di-list |
| trx_id | String | Unique string alphanumeric |

**Response:**
```json
{
    "status": "200",
    "status_message": "Success",
    "faces": [
        {
            "user_id": "NIK123456789",
            "user_name": "John Doe"
        },
        {
            "user_id": "NIK987654321",
            "user_name": "Jane Smith"
        }
    ]
}
```

---

## C. Tips Implementasi

### Header Configuration

Pada setiap request API, pastikan untuk selalu menambahkan header berikut:

```
Accesstoken: [YOUR_TOKEN_HERE]
Content-Type: application/json
```

### Error Handling

Pastikan untuk menangani berbagai status code yang mungkin dikembalikan:

- **200**: Success
- **400**: Request malformed
- **401**: Access token not authorized
- **403**: Requested resource denied
- **412**: Face not detected
- **413**: Face too small

### Best Practices

1. **Validasi Image**: Pastikan image yang di-upload memiliki kualitas yang baik dan wajah terlihat jelas
2. **Unique Transaction ID**: Gunakan transaction ID yang unik untuk setiap request untuk memudahkan debugging
3. **Error Logging**: Implementasikan logging untuk setiap response API
4. **Token Security**: Jangan expose token di frontend, gunakan backend proxy

---

## D. Kesimpulan

Layanan Face Recognition dari Biznet Gio cocok untuk berbagai kebutuhan seperti:

- **Sistem Absensi Perusahaan**
- **Kontrol Akses Gedung**
- **Verifikasi Identitas**
- **Sistem Keamanan**

Dengan mengikuti tutorial ini, Anda sudah dapat:
- Membuat dan mengelola FaceGallery
- Mendaftarkan user dengan data wajah
- Menggunakan API untuk verifikasi dan identifikasi

---

## E. Dukungan

Jika Anda membutuhkan bantuan lebih lanjut:

- **Knowledge Base**: [Biznet Gio Knowledge Base](https://support.biznetgio.com/)
- **Email Support**: support@biznetgio.com
- **Telepon**: (021) 5714567

---

## F. Dokumentasi Terkait

- [Biznet Face API Documentation](./BIZNET-FACE-API.md)
- [Cara Berlangganan Layanan AI/ML (Face Recognition)](https://portal.biznetgio.com)
- [Postman Documentation](https://learning.postman.com/docs/getting-started/introduction/)
