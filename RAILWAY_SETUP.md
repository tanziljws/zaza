# Railway Deployment Setup

## PHP Extensions Configuration

Railpack secara otomatis mendeteksi Laravel, tetapi tidak menginstall semua extensions yang diperlukan. Untuk menambahkan extensions `gd`, `bcmath`, dan `zip`, tambahkan environment variable berikut di Railway Dashboard:

### Environment Variable (SOLUSI UTAMA)

Di Railway Dashboard → Service → Variables, tambahkan:

**Variable Name:** `RAILPACK_PHP_EXTENSIONS`  
**Variable Value:** `gd,bcmath,zip`

**Catatan:** Railpack akan otomatis menginstall extensions default (ctype, curl, dom, fileinfo, filter, hash, mbstring, openssl, pcre, pdo, session, tokenizer, xml, pdo_mysql, redis), jadi Anda hanya perlu menambahkan extensions tambahan: `gd`, `bcmath`, dan `zip`.

### Alternatif: Composer Ignore Platform Requirements

Jika tidak bisa menambahkan environment variable, `composer.json` sudah dikonfigurasi untuk mengabaikan platform requirements untuk `ext-gd`, `ext-bcmath`, dan `ext-zip`:

- `platform-check: false` - Mengabaikan pengecekan platform
- `--ignore-platform-req=ext-gd --ignore-platform-req=ext-bcmath --ignore-platform-req=ext-zip` - Mengabaikan requirement extensions

Composer akan tetap berjalan meskipun extensions tidak terinstall, tetapi aplikasi mungkin error saat runtime jika extensions benar-benar diperlukan.

## Database Configuration

Database sudah dikonfigurasi di `.env` untuk Railway MySQL:
- Host: `shortline.proxy.rlwy.net`
- Port: `26377`
- Database: `railway`
- Username: `root`

Database sudah diimport dengan 77 statements berhasil dieksekusi.

## Deployment

Setelah menambahkan environment variable `PHP_EXTENSIONS`, deployment seharusnya berjalan dengan baik.

