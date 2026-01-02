# waBlast - WhatsApp Reminder System

Dashboard untuk mengelola pengiriman pesan WhatsApp reminder ke pasien mengenai jadwal kontrol mereka.

## 📋 Fitur Utama

### 1. **Dashboard Data Pasien**
- Tampilan lengkap data pasien dari tabel `pasien`
- Fitur pencarian berdasarkan:
  - Nama pasien
  - No. Rekam Medis
  - No. KTP
  - No. Telepon
- Fitur filter berdasarkan:
  - Jenis Kelamin (L/P)
  - Status Pernikahan
- Pagination (15 pasien per halaman)
- Detail pasien dengan riwayat kunjungan medis
- Link WhatsApp langsung ke nomor pasien

### 2. **Dashboard Jadwal Kontrol BPJS**
- Tampilan lengkap jadwal kontrol dari tabel `bridging_surat_kontrol_bpjs`
- Integrasi data pasien, reg_periksa, dan bridging_sep
- Fitur pencarian berdasarkan:
  - Nama pasien
  - No. Rekam Medis
- Fitur filter berdasarkan:
  - Status pengiriman WA (Terkirim/Gagal/Belum Terkirim)
  - Tanggal rencana (dari-sampai)
- Statistik dashboard
- Pagination (15 jadwal per halaman)
- Fitur kirim reminder WhatsApp per pasien

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains over 1500 video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the Laravel [Patreon page](https://patreon.com/taylorotwell).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Cubet Techno Labs](https://cubettech.com)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[Many](https://www.many.co.uk)**
- **[Webdock, Fast VPS Hosting](https://www.webdock.io/en)**
- **[DevSquad](https://devsquad.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[OP.GG](https://op.gg)**
- **[WebReinvent](https://webreinvent.com/?utm_source=laravel&utm_medium=github&utm_campaign=patreon-sponsors)**
- **[Lendio](https://lendio.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
