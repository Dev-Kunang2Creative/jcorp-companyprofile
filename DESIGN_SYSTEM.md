# DESIGN SYSTEM — J-Corporate Group

## -2. Revisi media client — 9–10 September 2026

Bagian ini adalah tambahan yang mengikat untuk revisi media terbaru. Aturan di
bawah tidak mengganti identitas visual tiap unit; ia menambahkan tempat foto
yang konsisten dan mudah dikelola.

- Halaman induk dan setiap anak usaha mendukung satu **foto pembuka** yang
  dapat dikelola admin. Untuk lima anak usaha, aset bawaan saat ini adalah
  foto ilustratif fotorealistik yang dibuat khusus sesuai bidang usahanya.
  Foto ini hanya membangun konteks visual di hero; ia bukan bukti pekerjaan,
  produk, properti, armada, staf, maupun lokasi asli milik client.
- Foto pembuka anak usaha memakai sumber lebar `16:9` sebagai **background
  penuh section hero**, mengikuti interpretasi contoh pada slide 2 revisi
  client. Foto bukan kartu terpisah di samping teks. Teks berada langsung di
  atas foto dengan gradasi terang dari sisi kiri pada desktop; objek utama
  foto dijaga di sisi kanan. Pada mobile, titik fokus foto tetap di kanan dan
  gradasi menguat ke bagian bawah agar judul serta tombol tetap terbaca.
- Setiap anak usaha memakai Galeri/Portfolio. Satu foto galeri dapat ditandai
  **Tampil di halaman induk** dan menjadi gambar kartu unit usaha. Dalam satu
  unit hanya boleh ada satu foto yang ditandai.
- Galeri/Portfolio hanya memuat materi asli dari client atau unggahan admin.
  Foto ilustratif hero tidak boleh dimasukkan ke galeri, katalog, maupun
  pilihan gambar kartu halaman induk karena ketiganya terbaca sebagai bukti
  produk/hasil kerja yang benar-benar tersedia.
- Jika admin sudah memilih foto pembuka sendiri, proses impor tidak boleh
  menimpanya. Jika foto pembuka atau foto kartu belum tersedia, komposisi lama
  tetap dipakai; tidak ada placeholder data atau klaim pekerjaan yang dikarang.
- Materi Sweetness Things dan ngelash.id yang diterima saat ini berbentuk
  poster vertikal. Poster hanya ditampilkan utuh di galeri dengan rasio
  portrait; poster tidak otomatis menjadi foto pembuka atau gambar kartu pada
  halaman induk.
- Bagian bawah halaman memakai gradasi lembut dari warna dunia masing-masing
  menuju putih. Gradasi tidak boleh mengurangi keterbacaan isi atau membuat
  satu warna baru terlihat sebagai warna resmi merek.
- Desktop memakai teks di sisi kiri di atas background penuh. Mobile
  menempatkan teks di bagian bawah background yang sama; tidak ada kartu foto
  terpisah dan tidak ada gulir horizontal.
- Warna aksen yang sudah tersimpan tetap dipakai sampai client memberikan
  warna resmi. Foto baru bukan dasar untuk menebak atau mengganti warna merek.

Materi asli yang tersedia: tujuh poster ngelash.id dan empat poster Sweetness
Things. Materi ini tidak otomatis menjadi item katalog karena hubungan antara
nama pada poster dan produk/layanan di database belum dikonfirmasi client.
Lima foto pembuka yang dibuat pada 10 September tetap diberi batas penggunaan
sebagai ilustrasi hero dan tidak mengubah status materi asli tersebut.

**Dibuat:** 11 Agustus 2026 · **Fase 2**
**Direvisi:** 12 Agustus 2026 — glassmorphism diperluas ke seluruh aplikasi
**Direvisi:** 23 Agustus 2026 — **seluruh aplikasi pindah ke arah A+B** (§0), panel admin ikut
**Direvisi:** 23 Agustus 2026 — **homepage induk memakai Luminous Editorial Glass** (§-1)
**Direvisi:** 24 Agustus 2026 — **Nail's by Me memakai One Family, Five Signatures** (§-0.5)
**Direvisi:** 24 Agustus 2026 — **hero induk dan Nail's memakai Editorial Settle + Brand Signature**
**Direvisi:** 24 Agustus 2026 — **empat anak usaha lain memakai family shell dan signature masing-masing** (§-0.4)
**Direvisi:** 24 Agustus 2026 — **Ayodya bilingual, kontak logistik terstruktur, galeri ngelash, dan tagline kartu induk** (§-0.4)
**Direvisi:** 24 Agustus 2026 — **Five Editorial Worlds — Luminous Pastel Edition menggantikan komposisi child page yang terlalu seragam** (§-0.3)
**Direvisi:** 24 Agustus 2026 — **content/order lock: visual boleh dirombak, kata dan urutan section tetap** (§-0.3)
**Direvisi:** 24 Agustus 2026 — **locale-safe reveal Ayodya dan proporsi panel Tentang J-Land diperbaiki** (§-0.4)
**Direvisi:** 24 Agustus 2026 — **grid ganjil, logo kontak, dan headline kontak desktop diseimbangkan** (§-1, §-0.4)
**Direvisi:** 27 Agustus 2026 — **navbar publik lebih opak agar konten di belakang tidak mengganggu keterbacaan** (§-1, §-0.3)

> Dokumen ini **mengikat**. Saat menulis kode UI, ikuti angka di sini — jangan menentukan warna, ukuran huruf, atau jarak baru secara mandiri. Kalau ada yang terasa kurang pas saat diterapkan, ubah dokumen ini lebih dulu, baru kodenya.

---

## -1. OVERRIDE TERKINI — HOMEPAGE INDUK

Homepage induk (`/`) memakai arah **Luminous Editorial Glass**: putih terang, komposisi editorial, glassmorphism selektif, dan motion sinematik yang halus. Bagian ini mengalahkan aturan §0–§6 **hanya untuk homepage induk**.

Kelima halaman anak usaha memakai override **Five Editorial Worlds — Luminous Pastel Edition** pada §-0.3; §-0.5 dan §-0.4 menjadi catatan kontrak data serta keputusan terdahulu yang masih berlaku bila tidak bertentangan. Panel admin, auth, serta halaman error tetap memakai arah A+B. Pembatasan scope ini mencegah satu revisi homepage mengubah visual bagian lain secara diam-diam.

Aturan yang mengikat untuk homepage induk:

- Urutan section: **Beranda → Tentang → Visi & Misi → Unit Usaha → Direksi → Hubungi Kami**
- Tipografi tetap **Instrument Serif + Inter Tight**
- Latar tetap dominan `#FFFFFF`; warna hangat hanya sebagai cahaya ambien tipis
- Glassmorphism dipakai selektif pada navbar, panggung logo, kartu pernyataan, kartu unit, dan panel kontak
- Navbar induk memakai satu permukaan putih dengan opasitas `94%` dan backdrop blur `16px` di desktop / maksimal `8px` di mobile. Tanpa dukungan blur, saat menu mobile terbuka, atau ketika pengguna memilih reduced transparency, permukaannya solid. Logo dan teks tidak diberi opacity atau blur; ukuran, posisi, serta animasi navbar tetap
- Data unit memakai `accent_color` dari backend; tidak ada warna unit yang ditanam langsung di komponen
- Headline dan logo hero wajib terlihat sejak frame pertama tanpa opacity nol, blur, mask, atau reveal tertunda. Motion landing hanya satu kali pada navbar, garis label, CTA, label bidang, dan jumlah unit; ring serta logo tetap statis. Tidak ada aura/glow bergerak, morph logo, ring berputar, pill mengambang, parallax, atau animasi berulang
- **Content lock:** nama, tagline, deskripsi, visi, misi, data unit, serta kontak harus dirender persis dari props Laravel. Dilarang menambah, mengurangi, menerjemahkan, merangkum, atau mengarang copy/data. Data kosong membuat elemen tidak dirender
- Teks antarmuka hanya memakai label navigasi/section yang sudah disetujui serta label tindakan yang sudah ada, seperti `Lihat Unit Usaha`, `Tentang Kami`, `Lihat profil`, dan `WhatsApp`
- Label orbit yang disetujui adalah `Kuliner`, `Kecantikan`, `Logistik`, dan `Properti`; label dikirim backend dan warna pill tetap berasal dari `accent_color` unit. Lingkaran kecil hanya boleh tampil sebagai penanda warna di dalam keempat label tersebut; satelit dan titik dekoratif lain di luar logo tidak dipakai

Komposisi homepage yang disetujui dan tidak boleh dikembalikan ke layout generik:

- Navbar memakai tiga zona: identitas, navigasi utama, lalu CTA hitam `Hubungi Kami`
- Hero memakai slogan client sebagai headline editorial hitam–kuningan, dua CTA, panggung logo dengan dua garis orbit statis serta empat label bidang, dan jumlah unit yang dihitung dari data. Deskripsi client tidak diulang di hero karena seluruhnya sudah tersedia di Tentang
- Tentang memakai nama induk sebagai headline di kiri, paragraf client lainnya sebagai pullquote, paragraf pertama di kartu kaca kanan, serta metrik turunan `05 Unit Usaha` dan `01 Induk Usaha`
- Visi & Misi memakai quote visi di kiri dan satu kartu untuk setiap butir misi di kanan; teksnya tidak boleh ditulis ulang
- Baris indeks bernomor dan label di ujung kanan section tidak dipakai. Setiap section langsung dibuka label garis berukuran `14–16px`: `Tentang Kami`, `Visi`, `Misi`, `Unit Usaha`, atau `Kontak`
- Headline nama induk di Tentang wajib dua baris: `J-Corporate` lalu `Group`; ukurannya lebih tenang daripada headline hero
- **Unit Usaha tetap memakai grid kartu compact yang sudah ada**, bukan daftar horizontal panjang dari prototype. Jika ada foto asli yang dipilih admin, foto tersebut menjadi background penuh kartu dengan overlay agar logo, nama, dan CTA tetap terbaca; unit tanpa foto tetap memakai kartu identitas biasa.
- Direksi memakai satu panel editorial dengan placeholder foto, nama, jabatan, dan profil singkat sampai materi resmi client diterima. Placeholder tidak boleh mengandung nama, foto, atau jabatan rekaan.
- Hubungi Kami memakai headline editorial dan susunan dua kolom; label garis `Kontak` berada di luar serta di atas panel glassmorphism, sedangkan seluruh akun dan detail kontak tetap berasal dari props Laravel
- Headline `Hubungi Kami` pada panel kontak induk wajib satu baris di desktop agar bidang atas tidak menyisakan ruang kanan berlebihan. Pada mobile kedua kata kembali bertumpuk supaya tidak menimbulkan overflow
- Container editorial memakai maksimum `1380px`, sedangkan grid Unit Usaha tetap `max-w-6xl` agar kepadatannya tidak berubah

Implementasi CSS wajib diawali `home-` dan komponen khusus homepage ditempatkan di `resources/js/components/public/home/`, supaya aturan ini tidak bocor ke halaman anak usaha.

---

## -0.3. OVERRIDE TERKINI — FIVE EDITORIAL WORLDS, LUMINOUS PASTEL EDITION

Kelima anak usaha tetap terasa berasal dari satu induk melalui tipografi, kualitas material, aksesibilitas, dan kontrak data yang sama. Namun, komposisi, ritme section, geometri, atmosfer pastel, serta motion signature tidak lagi memakai template presentasi identik. Target baru adalah sekitar **40% family system dan 60% dunia visual khas brand**.

Aturan warna untuk setiap child page adalah **55% white canvas, 35% pastel atmosphere, dan 10% deep brand accent**. Porsi 35% tidak berarti seluruh teks atau kartu menjadi pastel: pastel hadir sebagai bidang latar besar, panel, dan atmosfer statis, sedangkan aksen pekat tetap dipakai pada CTA, garis, label, serta fokus penting agar kontras dan identitas tidak melemah.

| Anak usaha                | Dunia editorial                                                       | White canvas | Pastel atmosphere                               | Aksen pekat       |
| ------------------------- | --------------------------------------------------------------------- | ------------ | ----------------------------------------------- | ----------------- |
| Sweetness Things          | premium patisserie catalogue; kurva dan kartu katalog compact         | `#FFFDF7`    | butter cream `#F5E6BD`, peach wash `#FFF4DC`    | backend `#6B4F0F` |
| Nail's by Me              | precision beauty portfolio; nail-tip dan garis vertikal presisi       | `#FCFEF9`    | sage `#DCEACB`, mint `#EFF6E8`                  | backend `#2A4104` |
| ngelash.id                | fashion beauty lookbook; lash sweep dan ritme editorial               | `#FFFDFB`    | champagne blush `#F3DFD3`, soft blush `#FBF1EB` | backend `#7A5C12` |
| PT. Ayodya Utama Logistic | route and operations profile; jalur horizontal serta node terstruktur | `#FFFDFD`    | coral mist `#F6DADD`, soft red `#FDF0F1`        | backend `#9E1B21` |
| J-Land Property           | architectural dossier; grid denah serta modul geometris               | `#FBFDFF`    | powder blue `#DCEAF5`, mist blue `#EEF5FB`      | backend `#14488C` |

### Komposisi yang mengikat

- **Sweetness Things:** hero memiliki panggung logo bundar dan overlap editorial; katalog tetap pada posisi informasi yang sudah disetujui serta memakai ukuran kartu compact yang sama dengan Nail's by Me
- **Nail's by Me:** copy lebih sempit, panggung brand lebih lebar, detail nail-tip statis, dan galeri diperlakukan sebagai portfolio presisi dengan semua item tetap seragam
- **Daftar harga Nail's by Me:** selalu berupa tabel dua kolom tanpa foto pada halaman publik, dengan nama/keterangan di kiri dan harga di kanan. Warna krem-hijau mengikuti materi client, tetapi tipografi tetap memakai sistem website agar mudah dibaca di desktop dan mobile
- **ngelash.id:** logo menjadi fashion stage di kiri, copy berada di kanan, daftar layanan/harga terasa seperti editorial menu, dan galeri memakai sapuan lengkung tanpa membesarkan item pertama
- **Ayodya:** headline dan informasi utama tersusun di atas wide route-logo band; layanan menjadi baris operasional horizontal. Switch `ID / EN`, isi bilingual yang sudah disetujui, serta data kontak terstruktur tetap dipertahankan
- **J-Land:** panggung logo dan copy memakai grid arsitektural; kartu layanan, keunggulan, dan panel lain disusun seperti dossier/modul denah
- Empat halaman pada `BrandBusinessPage` mempertahankan urutan: **Tentang → Visi & Misi → Layanan Unggulan → Keunggulan → Layanan → Katalog/Harga → Galeri → Kontak**. Section tanpa data dilewati, tetapi section lain tidak boleh dipindahkan untuk membangun narasi baru
- Nail's by Me mempertahankan urutan yang sudah disetujui pada implementasi khususnya; Galeri tetap tepat setelah Profil Layanan dan sebelum Katalog/Harga. Revisi visual tidak boleh memindahkan urutan tersebut
- Navbar masing-masing child page memiliki siluet khas brand, tetapi perilaku keyboard, hamburger, target sentuh, link aktif, dan CTA tetap memakai kontrak aksesibilitas bersama

### Material, motion, dan performance

- Glassmorphism tetap selektif. Blur maksimum per dunia: Sweetness `18px`, Nail's `16px`, ngelash `16px`, Ayodya `12px`, dan J-Land `10px`; kartu berulang tidak diberi blur berat
- Navbar kelima anak usaha dipisahkan dari opasitas kartu: satu permukaan tint putih khas brand dengan opasitas `94%`, tetap memakai batas blur tiap brand. Fallback tanpa blur dan menu hamburger terbuka wajib solid, termasuk ukuran tablet sampai `70rem`. Siluet navbar tertutup, warna aksen, ukuran, dan animasi tidak berubah; isi logo/menu tetap tajam tanpa opacity atau blur pada elemen induknya. Khusus menu Sweetness yang terbuka, radius `1.25rem` berlaku sampai `70rem` agar seluruh logo, tautan, dan tombol tutup berada di dalam permukaan yang sama
- Bidang pastel, lash sweep, route line, blueprint grid, dan nail-tip adalah dekorasi CSS statis. Tidak ada particle system, orbit, parallax, atau animasi tanpa akhir
- Landing motion hanya satu kali dan berbasis `transform`/`opacity` pada elemen pendukung. Sweetness settle berlapis, ngelash sweep, Ayodya route draw, dan J-Land plan assemble; headline utama serta logo tetap terlihat sejak frame pertama
- Mobile memakai satu kolom, dekorasi lebih kecil, blur maksimal `8px`, serta tidak boleh menghasilkan horizontal overflow. `prefers-reduced-motion` dan `prefers-reduced-transparency` tetap wajib
- Tidak ada dependency animasi baru. CSS signature berada pada cascade layer `editorial-worlds` agar override visual tidak bocor ke homepage induk atau panel

### Data dan copy lock

- Seluruh nama, deskripsi, visi, misi, layanan, harga, galeri, caption, kontak, dan logo tetap persis dari props/database yang sudah disetujui client
- Tidak boleh menambah label sektor, slogan, produk, harga, foto, partner, wilayah layanan, ataupun placeholder baru pada child page
- Shared React primitives dan urutan informasi boleh tetap sama. Kekhasan lima dunia wajib dibangun melalui komposisi internal, warna, geometri, material, dan motion—bukan melalui pemindahan section atau penambahan copy
- Katalog bergambar seluruh child page memakai batas ukuran konsisten: `3` kolom desktop, `2` kolom tablet, dan `1` kolom mobile. Satu atau dua item tidak boleh melebar mengisi container; karakter brand tetap berasal dari radius, warna, material, dan detail kartu

Bagian ini mengalahkan rasio lama `70/30`, susunan hero dua kolom generik, dan keputusan "template seragam" pada §-0.5, §-0.4, serta catatan historis lain. Content lock, bilingual Ayodya, galeri ngelash/Nail's, aksesibilitas, dan seluruh keputusan data yang tidak bertentangan tetap berlaku.

---

## -0.5. KONTRAK DATA TERDAHULU — NAIL'S BY ME

Halaman `/nails-by-me` menjadi implementasi pertama arah **One Family, Five Signatures**. Arah presentasinya sekarang disempurnakan oleh §-0.3, sedangkan aturan data, galeri seragam, aksesibilitas, dan content lock pada bagian ini tetap mengikat. Ia tetap satu keluarga dengan homepage induk melalui latar putih terang, Instrument Serif + Inter Tight, label section bergaris, glassmorphism selektif, dan motion transform/opacity.

Aturan khusus pada bagian ini berlaku untuk slug `nails-by-me`; empat signature lain diatur di §-0.4. Kelimanya tetap memakai route dan kontrak props yang sama.

### Token visual

| Peran           | Nilai                                      |
| --------------- | ------------------------------------------ |
| Aksen utama     | `accent_color` backend; saat ini `#2A4104` |
| Aksen gelap CTA | `#203303`                                  |
| Hijau kabut     | `#DCE7D7`                                  |
| Hijau pucat     | `#EDF3E9`                                  |
| Teks utama      | `#151712`                                  |
| Teks sekunder   | `#5C6258`                                  |
| Container       | maksimum `1200px`                          |
| Display         | Instrument Serif 400                       |
| UI & isi        | Inter Tight 400/500/600                    |

`#2A4104` terhadap putih memiliki rasio 10,75:1. Teks sekunder `#5C6258` juga melewati WCAG AA terhadap putih. CTA putih di atas aksen hijau memakai pasangan yang sama dan tidak boleh diganti dengan hijau logo yang lebih terang tanpa mengukur ulang kontras.

### Komposisi dan content lock

- Hero dua kolom: headline client di kiri, logo asli di panggung kaca berbentuk nail-tip di kanan
- Headline dan logo hero wajib terlihat sejak frame pertama; logo dimuat eager dengan `fetchpriority="high"`. Motion landing hanya satu kali pada navbar, garis label, CTA, bentuk nail-tip di belakang vessel, dan garis signature. Vessel serta logo tetap statis; tidak ada partikel, ring berputar, atau animasi berulang
- Tentang memakai headline editorial di kiri dan deskripsi client utuh di panel kaca kanan
- Visi tetap satu kutipan utuh; setiap misi menjadi satu kartu bernomor tanpa menulis ulang kalimatnya
- Keunggulan memakai bento lima kartu; Profil Layanan memakai tiga kartu dengan motif nail swatch statis
- Galeri memakai data `portfolio` backend dan berada tepat setelah Profil Layanan. Label antarmukanya `Galeri`, sedangkan judul besarnya tetap memakai `portfolio_label` dari database (saat ini `Hasil Kerja`)
- Galeri memakai grid seragam tanpa foto unggulan: seluruh item memiliki ukuran, rasio `1:1`, dan radius yang sama. Setiap foto tetap dapat dibuka ke berkas ukuran penuh dan caption dirender persis dari database
- Katalog dan galeri hanya muncul bila props Laravel berisi data. Tidak ada foto stok, kartu contoh, harga contoh, atau section kosong
- Label Kontak berada di luar panel kaca. Seluruh nomor, akun sosial, alamat, jam, dan catatan tetap berasal dari props
- Label bergaris menjadi penanda section yang jelas: teks `16px` di mobile dan `18–20px` di desktop, dengan garis `60px` di mobile dan `72px` di desktop. Aturan ini berlaku konsisten pada homepage induk dan presentasi Nail's
- Tidak ada CTA mobile yang fixed/sticky di dasar layar karena menutupi isi. CTA hanya muncul pada hero, navbar desktop, dan panel kontak
- Teks profil, visi, misi, keunggulan, layanan, katalog, portfolio, serta kontak tidak boleh ditambah, dikurangi, diringkas, diterjemahkan, atau ditanam langsung di komponen

### Glassmorphism dan performance

- Blur dibatasi pada navbar, panggung logo, panel Tentang, kartu misi, dan panel Kontak
- Kartu berulang seperti keunggulan, layanan, katalog, serta portfolio memakai permukaan transparan/padat tanpa `backdrop-filter`
- Reveal scroll tetap hanya berlaku di bawah hero. Landing hero memakai individual transform CSS (`translate`, `rotate`, `scale`) tanpa menyembunyikan headline atau logo; efek blur global dinonaktifkan di scope Nail's
- `prefers-reduced-motion` mematikan transisi; `prefers-reduced-transparency` mengubah seluruh permukaan kaca menjadi putih padat

### Motion landing hero

- Arah: **Cinematic Balanced — Editorial Settle + Brand Signature**, berjalan satu kali saat halaman dimuat dan berhenti total setelah selesai
- Easing khusus landing memakai `cubic-bezier(.2, .65, .25, 1)`: awal gerak mudah terbaca, lalu melambat lembut tanpa terasa memantul. Mobile menjadi baseline dengan urutan selesai sekitar `2.45s`; desktop memakai jarak maksimal `12–16px` dan keseluruhan urutan selesai sekitar `2.95s`
- Homepage induk: navbar settle, garis label memanjang, CTA naik halus, empat label bidang masuk searah jarum jam dengan stagger yang jelas (`Kuliner → Kecantikan → Logistik → Properti`), lalu jumlah unit menjadi penutup. Ring, logo, dan headline tetap diam
- Nail's by Me: navbar settle, garis label memanjang, CTA naik halus, lalu empat bentuk nail-tip bergerak dalam tiga fase `gathered → open → settle`. Garis signature baru tergambar setelah fan hampir terbentuk. Vessel, logo, dan headline tetap diam
- Navbar memakai settle yang lebih panjang daripada elemen pembuka lain: `850ms` pada mobile dan `1100ms` pada desktop, tanpa opacity reveal
- Opacity awal `0.45–0.55` hanya boleh dipakai pada elemen pendukung yang bergerak. Headline dan logo wajib langsung `opacity: 1` tanpa animation agar kandidat LCP tidak ditunda
- Seluruh gerak memakai CSS, tidak menambah library atau state React. `prefers-reduced-motion: reduce` mematikan semua landing animation dan langsung menampilkan keadaan akhir

### Responsive

| Bagian      | Desktop                                        | Mobile                                  |
| ----------- | ---------------------------------------------- | --------------------------------------- |
| Navbar      | identitas + tautan section yang tersedia + CTA | identitas + menu hamburger, target 45px |
| Hero        | dua kolom, panggung logo ±590px                | satu kolom, CTA penuh, panggung ±390px  |
| Tentang     | headline sticky + panel teks                   | satu kolom, headline di atas panel      |
| Visi & Misi | visi kiri, tiga kartu kanan                    | seluruhnya satu kolom                   |
| Keunggulan  | bento 2 + 3 kartu                              | satu kartu per baris                    |
| Layanan     | tiga kolom                                     | satu kartu per baris                    |
| Galeri      | empat kolom seragam                            | dua kolom seragam                       |
| Kontak      | catatan + detail kontak                        | satu kolom, CTA penuh                   |

Komponen bersama arah ini ditempatkan di `resources/js/components/public/signature/`; CSS wajib diawali `signature-` atau `nails-`. Pemilihan variant tetap terjadi dari halaman `public/business.tsx`, sehingga route serta kontrak data publik tidak diduplikasi.

---

## -0.4. KONTRAK DATA TERDAHULU — EMPAT SIGNATURE ANAK USAHA

Sweetness Things, ngelash.id, PT. Ayodya Utama Logistic, dan J-Land Property mula-mula melanjutkan arah **One Family, Five Signatures** memakai satu family shell bersama. Rasio `70/30` dan komposisi seragamnya telah digantikan §-0.3. Bagian ini tetap menjadi sumber keputusan untuk data, bilingual Ayodya, galeri, serta content lock yang tidak bertentangan dengan arah terbaru.

### Pemetaan signature

| Slug               | Aksen backend | Karakter visual                                                     | Penerapan utama                                                                     |
| ------------------ | ------------- | ------------------------------------------------------------------- | ----------------------------------------------------------------------------------- |
| `sweetness-things` | `#6B4F0F`     | emas hangat, kurva lembut, lapisan lingkaran seperti piring dessert | vessel logo bundar, panel membulat, katalog/daftar menu editorial                   |
| `ngelash`          | `#7A5C12`     | emas gelap, sapuan lengkung seperti lash sweep, fashion-editorial   | vessel melengkung, garis sweep statis, daftar layanan dan harga yang mudah dipindai |
| `ayodya-logistic`  | `#9E1B21`     | merah, garis rute horizontal, geometri tegas dan terstruktur        | vessel logo lebar, node rute statis, kartu layanan modular                          |
| `j-land-property`  | `#14488C`     | biru, grid/garis denah arsitektural, geometri presisi               | vessel persegi, motif floor-plan, kartu layanan dan keunggulan geometris            |

### Aturan komposisi dan content lock

- Hero selalu memakai nama, tagline bila tersedia, serta logo dari props Laravel. Nama dan logo terlihat sejak frame pertama; tidak ada opacity nol, blur, mask, atau reveal tertunda pada kandidat LCP
- Tidak ada label sektor baru seperti `Kuliner`, `Beauty`, `Logistic`, atau `Property`, dan tidak ada deskripsi buatan. Identitas sektor hanya disampaikan melalui bentuk dan warna. Pengecualian copy baru hanya tiga tagline Inggris pada kartu Unit Usaha induk yang disetujui 24 Agustus 2026; tagline ini tidak mengubah kolom database atau hero anak usaha
- Tentang, Visi & Misi, Layanan Unggulan, Keunggulan, Profil Layanan, katalog/daftar harga, galeri, serta kontak hanya dirender bila datanya tersedia
- Semua paragraf, misi, nama layanan, deskripsi, harga, caption, kontak, dan label database dirender persis dari props. Komponen tidak menerjemahkan, merangkum, menggabungkan, atau mengganti data kosong
- Satu komponen `BrandBusinessPage` dan dua kelompok section presentasional menangani keempat halaman. Pemilihan variant hanya berdasarkan slug yang sudah dikenal; route, controller, model, dan kontrak data tidak diduplikasi
- Galeri, bila tersedia, selalu grid seragam `1:1`; tidak ada item pertama yang diperbesar
- Pada kartu katalog bergambar, harga atau teks pengganti harga dan CTA WhatsApp menjadi satu footer yang selalu menempel di dasar kartu. Panjang judul/deskripsi tidak boleh menggeser posisi footer terhadap kartu lain dalam baris yang sama
- Pada desktop J-Land, panel cerita Tentang mendapat kolom baca dominan (`0.9fr / 1.1fr`, minimum panel `36rem`) dan gap `4rem`. Headline tetap menjadi jangkar arsitektural, tetapi panel tidak boleh kembali sempit hingga membuat section sangat tinggi dan menyisakan ruang kosong berlebihan
- Panel cerita Tentang ngelash memakai proporsi desktop yang sama (`0.9fr / 1.1fr`, minimum panel `36rem`, gap `4rem`) sambil mempertahankan offset vertikal dan radius lash-sweep khasnya
- Daftar dua kolom dengan jumlah ganjil tidak boleh meninggalkan modul terakhir sendirian di setengah baris. Misi ke-7 ngelash menutup dua kolom; `Jual Rumah` pada Layanan Unggulan J-Land menjadi strip arsitektural penuh dan compact. Pada mobile seluruh item tetap satu kolom biasa
- Panel Kontak Sweetness dan J-Land menampilkan logo asli tepat setelah CTA di kolom kiri untuk menyeimbangkan daftar detail di kanan. Logo berasal dari `logo_url` props, bukan aset atau copy baru; child page lain tidak otomatis ikut menampilkan logo. Ukurannya `11rem` pada mobile dan `16rem` pada desktop. Sweetness mempertahankan vessel lingkaran, sedangkan J-Land tampil tanpa border, background, maupun padding tambahan

### Tambahan konten dan bahasa — 24 Agustus 2026

- Galeri `ngelash` berada setelah Profil Layanan dan `Layanan & Harga`. Label antarmuka memakai `Galeri`, sedangkan headline memakai `portfolio_label` database (`Hasil Kerja`). Section tetap hanya dirender ketika ada foto portfolio; tidak ada placeholder atau gambar contoh
- PT. Ayodya Utama Logistic mempunyai switch `ID / EN` di navbar, tepat di samping CTA WhatsApp pada desktop dan tetap tersedia di area aksi navbar mobile
- Bahasa Indonesia Ayodya selalu berasal langsung dari props/database dan menjadi sumber utama. Bahasa Inggris adalah terjemahan presentasi terpisah untuk deskripsi, visi, misi, layanan, kontak, navigasi, CTA, footer, serta pesan pembuka WhatsApp; terjemahan tidak menambah layanan, mitra, cakupan, atau data kontak baru
- Pilihan Inggris dapat dibagikan melalui `?lang=en`; bahasa Indonesia tetap menjadi default. Pergantian bahasa tidak memuat ulang halaman dan tidak menambah dependency
- Identity elemen daftar Ayodya harus stabil lintas locale. Perubahan ID/EN hanya mengganti isi node yang sama agar status reveal tetap aktif; key berbasis judul terjemahan dilarang karena membuat kartu baru tertahan pada `opacity: 0`
- Kontak Ayodya memecah catatan client menjadi baris informasi yang lebih mudah dipindai, tetapi urutan serta seluruh nama, nomor, telepon, fax, email, dan situs tetap utuh
- Tiga tagline fallback khusus kartu Unit Usaha induk:
    - Sweetness Things — `Homemade Desserts for Every Little Celebration.`
    - Nail's by Me — `Beautiful, Neat, and Long-Lasting Nail Art.`
    - J-Land Property — `Safe, Comfortable, and Trusted Property Solutions.`
- Tagline fallback tidak mengganti tagline yang diisi admin. Karena scope permintaan berada pada etalase induk, copy tersebut tidak ikut masuk ke hero anak usaha

### Glassmorphism, motion, dan performance

- Blur hanya dipakai pada navbar, vessel logo, panel Tentang, kartu Misi, daftar harga tanpa gambar, dan panel Kontak
- Kartu layanan, keunggulan, katalog bergambar, dan galeri memakai permukaan tanpa `backdrop-filter`
- Navbar settle `850ms` pada mobile dan `1100ms` pada desktop. Garis label, CTA, tagline pendukung, dan empat bagian motif masuk berurutan lalu berhenti total
- Sweetness menyelesaikan lapisan lingkaran dari luar ke dalam; ngelash menyusun empat sweep; Ayodya menggambar empat garis rute; J-Land menyusun empat bidang denah. Tidak ada loop, partikel, titik berjalan, parallax, atau elemen yang bergerak di luar hero
- `prefers-reduced-motion` mematikan landing motion dan hover transform; `prefers-reduced-transparency` mengubah seluruh panel kaca menjadi putih padat

### Responsive

- Pada navbar mobile, identitas brand memakai ruang fleksibel sedangkan tombol hamburger selalu menempel di ujung kanan shell. Tombol tidak boleh mengikuti lebar tulisan brand atau jatuh ke kolom tengah
- Ketika menu anak usaha terbuka, shell berubah menjadi panel solid yang mudah dibaca, scroll halaman dikunci, dan area di luar panel dapat diketuk untuk menutup menu. Geometri khas desktop—termasuk kapsul bundar Sweetness—tidak boleh membesar mengikuti tinggi daftar menu
- Perubahan bentuk navbar mobile tidak mengubah isi maupun urutan tautan section

| Bagian             | Desktop                                                        | Mobile                                                 |
| ------------------ | -------------------------------------------------------------- | ------------------------------------------------------ |
| Navbar             | identitas, tautan section yang tersedia, CTA WhatsApp          | identitas + hamburger dengan target 45px               |
| Hero               | dua kolom; vessel mengikuti rasio logo                         | satu kolom; CTA penuh; motif diperkecil tanpa dipotong |
| Tentang            | judul sticky kiri + panel cerita kanan                         | judul lalu panel cerita                                |
| Visi & Misi        | visi sticky kiri + kartu misi kanan                            | seluruhnya satu kolom                                  |
| Layanan/keunggulan | jumlah kolom mengikuti jumlah data, maksimum 3/4               | satu kartu per baris                                   |
| Katalog            | tiga kolom bila ada gambar; daftar editorial bila tanpa gambar | satu kolom/daftar vertikal                             |
| Galeri             | empat kolom seragam                                            | dua kolom seragam                                      |
| Kontak             | catatan + daftar detail                                        | satu kolom, CTA penuh                                  |

Komponen berada di `resources/js/components/public/signature/`; CSS family shell memakai awalan `brand-`, sedangkan elemen bersama memakai `signature-`. Token aksen tetap berasal dari `Business::safeAccentColor()` dan dipasang sebagai `--unit-accent`.

---

## 0.A PANEL ADMIN — CALM OPERATIONS STUDIO

**Direvisi 25 Agustus 2026.** Panel memakai arah **80% Calm Operations Studio + 20% Brand Studio**. Ini ruang kerja yang harus cepat dipindai, bukan versi mini halaman publik.

### Struktur shell

- Sidebar dikelompokkan berdasarkan tugas: **Ringkasan**, **Konten**, dan **Manajemen**. Label generik `Platform` tidak dipakai lagi
- Topbar selalu menunjukkan usaha aktif, status `Tayang` atau `Belum terbit`, aksen usaha, dan tautan menuju website publik
- Pemilih usaha super-admin bersifat global. Perpindahan tetap memakai kontrak `?business=` dan sesi yang sudah ada; business_admin tidak pernah menerima daftar usaha lain
- Warna induk menjadi aksen utama panel. Warna anak usaha hanya menjadi strip, swatch, dan penanda konteks—tidak mengubah seluruh canvas

### Halaman kerja

- Dashboard berisi status, jumlah konten, aksi cepat, peringatan data yang kosong, dan ringkasan seluruh usaha untuk super-admin
- Activity feed tidak dibuat selama backend belum mempunyai catatan aktivitas yang nyata
- Daftar katalog, galeri, akun, dan anak usaha tampil sebagai **tabel pada desktop** dan **kartu pada mobile**. Kedua bentuk membawa data dan aksi yang sama
- Form tambah/edit memakai drawer kanan di desktop dan layar penuh di mobile. Dialog konfirmasi yang berisiko tetap berada di tengah
- Form Kontak & Label memakai dua kolom pada desktop, satu kolom pada mobile, dan bar Simpan hanya muncul ketika form berubah

### Bahasa visual dan responsive

- Canvas putih/off-white; tabel dan form selalu padat tanpa backdrop blur
- Fraunces tetap menjadi display face, Jost tetap menjadi UI face
- Glassmorphism dibatasi ke sidebar, topbar, dan sticky save bar. Permukaan data tidak memakai kaca supaya teks stabil dan biaya render tetap rendah
- Transisi interaksi berada di `150–220ms`; tidak ada landing animation di panel
- Di bawah `768px`, tabel hilang dan kartu mobile menjadi representasi utama. Drawer memenuhi viewport, action button dapat disentuh dengan nyaman, dan topbar memadat tanpa menyembunyikan konteks usaha
- Semua route, policy, validasi, payload CRUD, konfirmasi publikasi, mekanisme undangan, serta sakelar galeri tetap menjadi kontrak logika yang tidak boleh berubah karena revisi visual

---

## 0. SATU PERMUKAAN, DUA TIPOGRAFI

Sejak 23 Agustus 2026 **seluruh aplikasi** memakai dasar permukaan yang sama: latar putih, kartu bergaris rambut, sudut nyaris siku. Krem dan aurora dipensiunkan. Sejak revisi 25 Agustus, kaca kembali hanya pada shell panel sebagaimana §0.A; tabel, kartu data, dan form tetap padat.

Yang masih berbeda hanya **tipografinya**, dan itu disengaja:

|                 | Halaman publik          | Panel admin & auth        |
| --------------- | ----------------------- | ------------------------- |
| **Untuk siapa** | Calon pelanggan, client | Anda dan admin anak usaha |
| **Latar**       | Putih `#FFFFFF`         | Putih `#FFFFFF`           |
| **Permukaan**   | Kartu garis rambut      | Kartu garis rambut        |
| **Display**     | Instrument Serif        | **Fraunces**              |
| **Antarmuka**   | Inter Tight             | **Jost**                  |

### Kenapa tipografinya tidak disamakan

**Instrument Serif hanya punya berat 400.** Panel butuh tebal untuk membedakan judul kolom tabel dari isinya — tanpa itu, tabel katalog berisi 15 layanan jadi satu blok abu-abu yang sulit dipindai. Fraunces punya 400/600/700.

**Inter Tight lebih rapat dari Jost.** Rapat itu bagus untuk judul besar di halaman publik; melelahkan untuk tabel padat yang dibaca berjam-jam.

Halaman publik dibuka sekali-dua kali oleh orang yang menilai; panel dipakai berjam-jam oleh orang yang bekerja. Tipografi adalah tempat perbedaan itu paling beralasan.

**Cara kerjanya di kode:** layout panel dan auth dibungkus `.font-panel`, yang mengembalikan Jost untuk seluruh isinya. Judul memakai `font-legacy-display` untuk Fraunces. Token `--font-legacy-*` di `app.css` jangan dihapus.

### Kenapa paletnya pindah

Palet emas dirancang untuk **Sweetness Things**, sebuah butik dessert. Palet yang sama lalu dipakai **PT. Ayodya Utama Logistic** — perusahaan pengiriman dengan Maersk dan NYK Line sebagai partner — dan **J-Land Property**.

§8.3 dokumen ini sudah menuliskan aturannya sejak Fase 2: _"Palet Sweetness (emas) hanya untuk Sweetness."_ Aturan itu tidak pernah dijalankan sampai keenam anak usaha punya materi asli, dan ketidakcocokannya jadi terlihat jelas.

### Catatan: kelas bernama `glass-*`

Kelas `.glass-panel`, `.glass-card`, `.glass-veil`, `.glass-raised`, dan `.glass-field` **tidak lagi berkaca** — definisinya di `app.css` sudah jadi permukaan padat. Namanya dipertahankan karena mengganti berarti menyunting 13 pemakaian di 7 berkas tanpa mengubah satu piksel pun hasilnya.

Di luar shell panel pada §0.A, satu-satunya yang masih memakai blur adalah `.glass-overlay` di belakang dialog. Di situ yang di-blur bukan latar polos, melainkan isi halaman — jadi masih ada yang dibiaskan.

---

## 1. Kepribadian (halaman publik)

**Terbitan perusahaan yang tidak perlu berteriak.**

Tiga kata kunci: **tenang, tegas, tertata.**

Yang membedakannya dari template company profile kebanyakan:

- **Tidak ada kotak yang menonjol.** Kartu berupa garis rambut 1px tanpa bayangan; kisi antar kartu yang membentuk strukturnya
- **Penomoran bab** `01 / 02 / 03` — halaman terbaca seperti terbitan yang disusun, bukan tumpukan section
- **Induk paling tenang, anak usaha yang berwarna.** Halaman induk hanya putih dan kuningan; tiap unit punya aksennya sendiri
- **Ukuran sebagai penarik perhatian**, bukan warna atau ornamen. Judul hero 78px di desktop

---

## 2. Warna (halaman publik)

```css
--color-paper: #ffffff; /* latar halaman */
--color-wash: #faf9f7; /* permukaan kartu — putih sedikit hangat */
--color-ink: #1a1a1a; /* teks utama */
--color-ink-soft: #57534b; /* teks sekunder */
--color-brass: #7c5f33; /* aksen induk — TEKS BOLEH */
--color-brass-line: #8a6a3b; /* garis & ikon besar saja — TEKS TIDAK */
--color-hair: rgb(26 26 26 / 0.12); /* garis */
--color-hair-soft: rgb(26 26 26 / 0.07); /* garis kisi kartu */
```

### 2.1 Kontras — diukur, bukan dikira

| Teks         | di `--paper` | di `--wash` | Syarat |     |
| ------------ | ------------ | ----------- | ------ | --- |
| `--ink`      | 17,40        | 16,54       | 4,5    | AAA |
| `--ink-soft` | 7,65         | 7,27        | 4,5    | AAA |
| `--brass`    | 5,93         | 5,64        | 4,5    | AA  |

**`#8A6A3B` GAGAL sebagai teks** — 4,43 di permukaan kartu, di bawah syarat 4,5. Itu warna yang sempat dipakai di mockup pertama. Hanya untuk garis dan ikon besar.

### 2.2 Aksen per anak usaha

Inti pergeseran ini. Tiap unit punya warnanya sendiri, diambil dari logonya lalu **ditua kan sampai lolos AA sebagai teks** — warna logo apa adanya hampir selalu terlalu terang.

| Unit                      | Aksen               | di `--wash` |
| ------------------------- | ------------------- | ----------- |
| J-Corporate (induk)       | `#7C5F33` kuningan  | 5,64        |
| Sweetness Things          | `#6B4F0F` emas      | 7,26        |
| ngelash.id                | `#7A5C12` emas tua  | 5,93        |
| Nail's by Me              | `#2A4104` hijau tua | 10,75       |
| PT. Ayodya Utama Logistic | `#9E1B21` merah     | 7,58        |
| J-Land Property           | `#14488C` biru      | 8,55        |

Contoh: merah logo Ayodya `rgb(227,43,43)` hanya **3,95** — cukup untuk gambar besar, gagal untuk teks. Yang dipakai versi tuanya, `#9E1B21`.

**Cara kerjanya di kode:**

1. Tersimpan di kolom `businesses.accent_color`, bukan ditanam di kode
2. Controller memanggil `Business::safeAccentColor()` — memeriksa bentuk `#RRGGBB`, dan mengganti yang salah dengan kuningan induk
3. Halaman memasangnya sekali di pembungkus sebagai `--unit-accent`
4. Komponen memakai `var(--unit-accent)` tanpa perlu tahu halaman mana yang memuatnya

> **KEAMANAN.** Nilai ini masuk ke atribut `style`. Panjang kolomnya dibatasi 7 karakter DAN bentuknya diperiksa dengan regex — dua lapis, karena satu saja tidak cukup. Ada 11 test di `AccentColorTest.php` yang menjaganya.

> **Menambah aksen baru: UKUR kontrasnya.** Ada test yang memeriksa bentuk hex-nya, tapi **tidak ada yang memeriksa kontrasnya.**

---

## 3. Tipografi (halaman publik)

| Peran     | Font                        | Alasan                                                                                        |
| --------- | --------------------------- | --------------------------------------------------------------------------------------------- |
| Display   | **Instrument Serif** 400    | Serif editorial bertekanan tinggi. Fraunces terlalu hangat untuk induk yang menaungi logistik |
| Antarmuka | **Inter Tight** 400/500/600 | Grotesk padat. Bukan Inter biasa — versi rapatnya punya karakter                              |

Keduanya SIL Open Font License, diunduh ke server sendiri saat build (`vite.config.ts`).

**Judul memakai `font-normal`, bukan bold.** Instrument Serif hanya punya satu berat, dan memaksa tebal lewat browser merusak bentuk hurufnya.

| Peran             | Desktop              | Mobile |
| ----------------- | -------------------- | ------ |
| Judul hero        | 78px                 | 42px   |
| Judul bab         | 34px                 | 24px   |
| Judul kartu       | 19–22px              | sama   |
| Isi               | 15px                 | 15px   |
| Label huruf besar | 10,5px, spasi 0,12em | sama   |

---

## 4. Komponen (halaman publik)

### Kartu garis rambut — `.hairline-card`

Garis 1px `--hair-soft`, latar `--wash`, **tanpa bayangan**, sudut siku. Margin `-0.5px` merapatkan kartu jadi satu kisi utuh — tanpa itu garis antar kartu menebal jadi dua piksel.

Saat disentuh: latar naik ke putih murni, terangkat 3px, pita aksen 2px tumbuh dari kiri.

> **Kartu naik ke putih, bukan sebaliknya.** Latar halaman sudah putih, jadi permukaan diamnya yang dibuat sedikit lebih hangat. Kalau dibalik, tidak akan ada bedanya.

**Di layar sentuh** (`hover: none`): pita aksen **selalu tampil** dan latarnya putih penuh. Tanpa itu pengunjung HP tidak akan pernah melihat warna unit usahanya.

### Judul bab — `chapter-heading.tsx`

Garis **padat** `--ink` di atas, nomor dua digit warna aksen, judul serif.

**Nomornya dihitung di halaman, bukan di komponen.** Section yang datanya kosong tidak dirender (spec §3), jadi menomori otomatis akan menghasilkan lompatan seperti 01, 03, 04 — yang terbaca sebagai ada sesuatu yang hilang.

### Tombol

Sudut `2px`, bukan kapsul. Warnanya `var(--unit-accent)` — jadi tombol di halaman Ayodya berwarna merah, bukan emas dessert.

Berlaku juga untuk tombol WhatsApp melayang di HP.

### Kartu anak usaha — `subsidiary-card.tsx`

Kartu di etalase induk, satu-satunya tempat kelima aksen berjajar bersamaan.

**Area logo bertinggi tetap 68px** dengan `object-contain`. Rasio kelima logo berjauhan — Ayodya 5,50:1, J-Corporate 0,99:1 — dan ditaruh apa adanya, tinggi kartunya berbeda-beda sehingga kisinya berantakan. Dengan tinggi tetap, yang memanjang menyusut lebarnya dan yang persegi menyusut tingginya, tapi baris teks di bawahnya selalu mulai di ketinggian yang sama.

`max-w-[200px]` menahan logo memanjang supaya tidak membentang selebar kartu dan menenggelamkan nama usahanya sendiri.

**Hanya tombolnya yang bisa diklik, bukan seluruh kartu.** Membungkus kartu dengan `<Link>` adalah praktik umum, tapi punya tiga akibat: teks di dalamnya tidak bisa diseleksi, pembaca layar membacakan seluruh isi kartu sebagai satu tautan panjang, dan tidak ada cara membuka tab baru dengan klik tengah di bagian yang terasa seperti teks biasa.

Efek hover tetap berlaku ke seluruh kartu lewat `group-hover`, jadi kartunya masih terasa satu kesatuan.

**Tombolnya bergaris bawah, bukan berlatar warna.** Aksen sudah dipakai nomor urut dan pita di tepi atas; lima tombol pekat berjajar membuat warnanya saling berebut perhatian.

**Membuka tab baru** (`target="_blank"` + `rel="noopener"`). Pengunjung yang melihat beberapa unit sekaligus tidak kehilangan halaman induk. `rel="noopener"` wajib — tanpa itu halaman yang dibuka bisa mengarahkan tab induk ke alamat lain.

> `<Link>` Inertia **mengabaikan** `target="_blank"` karena mengambil alih klik untuk perpindahan tanpa muat ulang. Pakai `<a>` biasa.

### Panah dan ikon kecil

**Gambar SVG, jangan karakter Unicode.** `↗` (U+2197) punya penyajian emoji bawaan di sebagian sistem — Windows merendernya biru-ungu, dan bentuknya berubah antar perangkat.

SVG selalu tampil sama, mewarisi warna teks lewat `currentColor`, dan ketebalannya bisa disamakan dengan garis rambut di sekitarnya (1,4px).

### Ikon tab browser (favicon)

Tiap halaman publik memakai **lencana usahanya sendiri**; panel admin memakai ikon bawaan.

Alasannya bukan hiasan: kartu anak usaha membuka tab baru, dan pengunjung yang melihat empat-lima unit sekaligus perlu tahu tab mana milik siapa tanpa membaca judul yang terpotong jadi beberapa huruf.

Jalurnya **diturunkan** dari jalur logo, bukan disimpan di kolom terpisah:

```
images/brand/ngelash-logo-600.webp  ->  images/brand/ngelash-badge-48.png
```

Berkas lencana selalu dibuat bersamaan dengan logonya oleh skrip di `docs/scripts/`, jadi polanya sudah pasti. Kolom kedua berarti dua daftar yang harus dijaga tetap seiring — dan cepat atau lambat keduanya berbeda.

> **Ditulis di `app.blade.php`, bukan lewat `<Head>` Inertia.** Lewat React, HTML awal memuat DUA `<link rel="icon">` — satu bawaan, satu dari React. Inertia menggantinya setelah JavaScript jalan, tapi browser sudah membaca yang pertama dan ikonnya berkedip berganti. Blade membaca `favicon_url` dari props dan menulis satu tag benar sejak respons pertama.

Berkas yang tidak ada dikembalikan `null` — favicon yang menunjuk ke berkas hilang membuat browser menampilkan ikon kosong, lebih buruk daripada memakai ikon induk.

### Navigasi

Komponen `SiteNav` yang sama dipakai halaman induk maupun anak usaha. Menulis navbar kedua khusus induk berarti dua tempat yang harus diperbaiki setiap kali ada perubahan — dan cepat atau lambat keduanya berbeda.

Menu induk: **Home · About Us · Support Company · Contact**. Tautan hanya menunjuk ke section yang benar-benar dirender; yang datanya kosong tidak muncul di navigasi (spec §3).

**Tautan jangkar butuh `scroll-margin-top`.** Bar navigasi menempel di atas (`sticky`), jadi tanpa jarak ini judul section berhenti tepat di baliknya dan tertutup separuh. Nilainya `5.5rem` pada `section[id]` — bukan `[id]` polos, yang akan mengenai kotak isian di panel admin juga.

`scroll-behavior: smooth` dimatikan di bawah `prefers-reduced-motion`: guliran panjang yang bergerak sendiri termasuk yang memicu pusing.

---

## 5. Motion (halaman publik)

```css
--ease-brand: cubic-bezier(0.22, 1, 0.36, 1); /* expo-out */
--duration-enter: 620ms;
```

| Hal               | Nilai                            | Kenapa                                            |
| ----------------- | -------------------------------- | ------------------------------------------------- |
| Jarak masuk       | **18px**                         | Bukan 60px. Kehalusannya justru dari jarak pendek |
| Jeda berurutan    | **60ms**, maks 360ms             | Cukup terasa berurutan tanpa membuat menunggu     |
| Ambang masuk      | 12% + `-6%` margin               | Selesai bergerak sebelum mata sampai ke situ      |
| Yang dianimasikan | `transform`, `opacity`, `filter` | Tidak ada yang memaksa hitung ulang tata letak    |

Satu `IntersectionObserver` untuk seluruh halaman (`use-reveal.ts`), bukan satu per komponen — halaman profil penuh punya ~40 elemen bergerak.

Animasi **tidak berulang**: `unobserve` setelah masuk.

### 5.1 `prefers-reduced-motion` — WAJIB

Sebagian orang mematikan animasi di tingkat OS karena membuat mereka pusing atau mual. Ditangani di dua tempat: CSS mematikan seluruh transisi, dan hook langsung menandai semua elemen sebagai sudah masuk.

Sebelum arah A+B, berkas CSS ini **tidak menangani `prefers-reduced-motion` sama sekali** — bisa dimaklumi selama tidak ada animasi, tidak lagi setelah ada.

### 5.2 Yang sengaja TIDAK dianimasikan

- **Angka statistik tidak menghitung naik.** Nilainya berasal dari jumlah anak usaha terbit, dan angka yang berubah saat dibaca membuat ragu apakah yang dilihat sudah final
- **Tidak ada parallax, tidak ada scroll-jacking, tidak ada section terkunci.** Calon klien logistik yang mencari nomor telepon tidak boleh dipaksa menonton animasi

---

## 6. Responsif (halaman publik)

Titik ubah **768px**.

| Elemen             | Desktop           | Mobile            |
| ------------------ | ----------------- | ----------------- |
| Judul hero         | 78px              | 42px              |
| Kartu keunggulan   | 3–4 kolom         | 1 kolom           |
| Tentang            | 2 kolom           | 1 kolom           |
| Aksen kartu        | muncul saat hover | **selalu tampil** |
| Latar kartu        | `--wash`          | putih penuh       |
| Navigasi           | tautan mendatar   | panel geser       |
| Tombol WA melayang | tidak ada         | ada               |

**Latar kartu di HP sengaja berbeda:** deretan blok abu-abu bertumpuk di layar sempit terlihat kotor, sedangkan di desktop blok itu justru yang membentuk kisinya.

---

> **BAGIAN DI BAWAH INI (§7–§17) ADALAH ARSIP.**
>
> Ditulis untuk tema krem + kaca yang berlaku 11 Agustus – 23 Agustus 2026, dan **tidak lagi menggambarkan tampilan aplikasi**. Sejak panel admin ikut pindah ke arah A+B, seluruh permukaan memakai §0–§6 di atas.
>
> Yang **masih berlaku** dari bagian ini:
>
> - **§14 Gambar** — aturan format, rasio, dan berkas logo keenam entitas
> - **§15 Nada tulisan** — bahasa Indonesia sopan tapi tidak kaku
> - **§16 Yang harus dihindari** — daftar ciri tampilan generik
>
> Sisanya (§7–§13, §17) disimpan sebagai catatan alasan, bukan acuan menulis kode. Angka kontras di §8.2 khususnya sudah tidak relevan: seluruhnya diukur terhadap permukaan kaca yang sudah tidak ada.

---

## 7. Kepribadian

**Butik pâtisserie yang percaya diri** — manis tapi punya harga diri, bukan manis yang murahan.

Tiga kata kunci yang memandu setiap keputusan: **hangat, tenang, rapi.**

Yang membedakannya dari template dessert kebanyakan:

- Gradasi menempel di **sudut**, tidak jadi latar penuh — terang tanpa terasa seperti template
- Serif tebal berkarakter (Fraunces), bukan serif netral
- Kaca punya **tiga tingkat ketebalan**, bukan satu opasitas yang ditempel rata di mana-mana
- Motif bintang dari logo dipakai sangat samar sebagai benang merah

---

## 8. Warna

### Token lengkap

```css
:root {
    /* Latar & permukaan */
    --cream: #fdfbf6; /* latar utama halaman */
    --cream-warm: #fbf7ef; /* permukaan alternatif */
    --white: #ffffff; /* kartu katalog, section tentang */

    /* Titik gradasi */
    --grad-gold: #f7e7c4; /* emas muda — sudut terang */
    --grad-blush: #f3e8dc; /* krem merah muda */
    --grad-rose: #f6e4e0; /* merah muda pucat — sudut kanan bawah */
    --grad-deep: #eadcc0; /* titik TERGELAP — acuan uji kontras */

    /* Emas — TIGA PERAN BERBEDA, jangan ditukar (lihat §2.1) */
    --gold: #b28c27; /* logo, garis, ikon besar. TIDAK untuk teks */
    --gold-mid: #8a6a15; /* judul >=24px saja */
    --gold-deep: #6b4f0f; /* semua teks emas & tombol */
    --gold-hover: #563f0b; /* tombol saat disentuh */

    /* Teks */
    --ink: #2e2a24; /* teks utama & judul */
    --ink-soft: #5b5348; /* teks sekunder & deskripsi */
    --ink-invert: #ffffff; /* teks di atas tombol emas */

    /* Garis — tembus pandang sejak revisi 12 Agu. Garis PADAT memotong
     permukaan kaca dan membuatnya terbaca sebagai kotak biasa yang
     kebetulan buram. */
    --line: rgba(160, 132, 74, 0.18); /* garis kartu & pemisah */
    --line-strong: rgba(160, 132, 74, 0.28); /* garis tombol & kotak isian */
    --line-glass: rgba(255, 255, 255, 0.7); /* tepi sorot panel kaca */

    /* Merah — untuk pesan galat & tombol hapus. Merah bata, bukan merah
     layar bawaan: yang terakhir menabrak seluruh palet hangat. */
    --danger: #9b2c1f;
}
```

### 8.1 Aturan emas — paling sering dilanggar

Logo Sweetness berwarna `#B28C27`. Warna itu **gagal WCAG AA sebagai teks** — rasionya hanya 3,15 di atas putih dan **2,32** di titik gradasi tergelap, sedangkan syaratnya 4,5.

Jadi emasnya dipecah tiga, dengan nada yang sama tapi ketuaan berbeda:

| Token                   | Boleh dipakai untuk                                           | Dilarang untuk                   |
| ----------------------- | ------------------------------------------------------------- | -------------------------------- |
| `--gold` `#B28C27`      | Logo, garis dekoratif, ikon ukuran besar, titik motif bintang | **Teks apa pun**, latar tombol   |
| `--gold-mid` `#8A6A15`  | Judul ≥24px di atas putih/krem                                | Teks kecil, teks di atas gradasi |
| `--gold-deep` `#6B4F0F` | Semua teks emas, latar tombol, garis tepi tombol              | —                                |

`--gold-deep` sudah diuji di **seluruh** titik gradasi: terburuk 5,63, di atas putih 7,64 (AAA).

### 8.2 Hasil pemeriksaan kontras

Sejak seluruh aplikasi berpermukaan kaca, ujinya bukan lagi satu opasitas melainkan **tiap tingkat kaca × tiap titik gradasi**. Angka di bawah adalah **yang terburuk** dari seluruh kombinasi itu — termasuk `--grad-deep`, titik tergelap.

Diuji ulang 12 Agustus 2026 dengan tingkat kaca di §5.1:

| Teks                                   | Kaca tipis (0,42) | Kartu (0,52) | Bar (0,55) | Panel (0,62) | Menonjol (0,80) | Syarat  |       |
| -------------------------------------- | ----------------- | ------------ | ---------- | ------------ | --------------- | ------- | ----- |
| `--ink` — judul & teks utama           | 12,00             | 12,37        | 12,48      | 12,75        | 13,45           | 4,5     | AAA   |
| `--ink-soft` — teks sekunder           | 6,37              | 6,56         | 6,62       | 6,76         | 7,14            | 4,5     | AA    |
| `--gold-deep` — eyebrow, harga, tautan | 6,42              | 6,62         | 6,68       | 6,83         | 7,20            | 4,5     | AA    |
| `--danger` — pesan galat               | 6,37              | 6,57         | 6,63       | 6,77         | 7,14            | 4,5     | AA    |
| `--gold-mid` — judul ≥24px saja        | 4,25              | 4,39         | 4,43       | 4,52         | 4,77            | **3,0** | lulus |

Pasangan yang tidak bergantung pada kaca:

| Pasangan                        | Rasio | Syarat |       |
| ------------------------------- | ----- | ------ | ----- |
| Teks putih di tombol emas       | 7,64  | 4,5    | AAA   |
| Teks putih di tombol saat hover | 9,95  | 4,5    | AAA   |
| Teks putih di tombol hapus      | 7,57  | 4,5    | AAA   |
| Inisial di kotak gambar gagal   | 6,71  | 3,0    | lulus |
| `--ink-soft` di krem polos      | 7,32  | 4,5    | AAA   |

**Perhatikan baris `--gold-mid`.** Di permukaan kaca angkanya turun ke 4,25 — **di bawah 4,5**. Itu masih benar karena §2.1 sudah membatasinya untuk judul ≥24px, yang syaratnya 3,0. Tapi artinya batasan itu sekarang **mengikat secara teknis, bukan sekadar kerapian**: memakai `--gold-mid` untuk teks kecil di atas kaca akan gagal AA. Untuk teks apa pun di bawah 24px, pakai `--gold-deep`.

**Satu pengecualian yang disengaja:** garis tepi panel kaca (`--line-glass`) rasionya hanya 1,11 terhadap gradasi. Dibiarkan samar dengan sadar — garis itu murni dekoratif, tidak membawa informasi, dan batas panel sudah jelas dari perbedaan warna latarnya sendiri. Menaikkan kontrasnya akan membuat garisnya keras dan merusak kesan kaca.

### 8.3 Anak usaha lain

Struktur dan komponen **identik**; yang berbeda hanya warna aksen dan titik gradasi. Saat menyusun palet anak usaha berikutnya, ulangi aturan §2.1: warna merek untuk elemen besar, varian lebih tua yang lolos AA untuk teks dan tombol.

Palet Sweetness (emas) **hanya untuk Sweetness**.

---

## 9. Tipografi

### Pasangan

| Peran           | Font                     | Alasan                                                                                          |
| --------------- | ------------------------ | ----------------------------------------------------------------------------------------------- |
| Display & judul | **Fraunces** 400/600/700 | Serif dengan karakter kuat — hangat dan sedikit nakal, cocok untuk dessert tanpa jadi kekanakan |
| Antarmuka & isi | **Jost** 400/500/600     | Geometris bersih. Logo sudah sangat ornamen; huruf antarmuka harus menahan diri (spec §7)       |

Keduanya **SIL Open Font License** — bebas untuk penggunaan komersial. Disediakan lewat Bunny Fonts (`@fonts` di Blade, diunduh ke server sendiri — tidak ada permintaan ke pihak ketiga saat pengunjung membuka halaman).

**Jangan** memakai Inter atau system-ui sebagai font utama — itu salah satu penanda tampilan generik.

### Skala

| Peran           | Desktop                                     | Mobile          | Font & tebal                            |
| --------------- | ------------------------------------------- | --------------- | --------------------------------------- |
| Judul hero      | 46px / 1,06                                 | 31px            | Fraunces 600, `letter-spacing: -.015em` |
| Judul section   | 30px / 1,15                                 | 24px            | Fraunces 600                            |
| Judul kartu     | 17px                                        | 15px            | Fraunces 600                            |
| Isi hero        | 16,5px / 1,65                               | 15px            | Jost 400                                |
| Isi umum        | 15,5px / 1,75                               | 15px            | Jost 400                                |
| Deskripsi kartu | 13,5px / 1,6                                | _disembunyikan_ | Jost 400                                |
| Eyebrow         | 11px, `letter-spacing:.22em`, HURUF BESAR   | sama            | Jost 500                                |
| Kategori kartu  | 10,5px, `letter-spacing:.16em`, HURUF BESAR | sama            | Jost 500                                |
| Tombol          | 15px                                        | 15px            | Jost 500                                |
| Harga           | 14,5px                                      | 14,5px          | Jost 500                                |

Lebar baris teks panjang dibatasi **`max-width: 42ch`** (hero) dan **`52ch`** (isi) supaya nyaman dibaca.

---

## 10. Jarak & sudut

Kelipatan 4. Jangan memakai angka di luar daftar ini.

```css
--s1: 4px;
--s2: 8px;
--s3: 12px;
--s4: 16px;
--s5: 24px;
--s6: 32px;
--s7: 48px;
--s8: 64px;
--s9: 96px;
```

| Penggunaan        | Desktop              | Mobile               |
| ----------------- | -------------------- | -------------------- |
| Padding section   | `--s8` `--s6`        | `--s7` `--s4`        |
| Padding hero      | `--s8` `--s6` `--s7` | `--s6` `--s4` `--s7` |
| Jarak antar kartu | `--s4`               | `--s3`               |
| Padding kartu     | `--s4`               | `--s3`               |

### Sudut membulat

```css
--r-sm: 10px; /* kotak kecil, tombol menu, kotak isian */
--r-md: 16px; /* kartu katalog & portfolio */
--r-lg: 24px; /* panel kaca */
--r-full: 999px; /* tombol — SEMUA tombol, termasuk di panel admin */
```

Sudutnya **tidak seragam** — panel kaca lebih membulat daripada kartu, dan tombol berbentuk kapsul penuh. Keseragaman sudut di semua elemen adalah salah satu penanda tampilan generik.

Ketiga angka pertama naik 2–4px pada revisi 12 Agustus. Alasannya bukan selera: sudut yang lebih membulat membuat tepi sorot `--glass-highlight` terbaca melengkung mengelilingi panel, dan itu yang memberi kesan lembaran kaca. Pada sudut 12px, sorot yang sama terlihat seperti garis lurus yang terpotong.

---

## 11. Glassmorphism — empat batasan mengikat

Spec §7 menetapkan empat batasan teknis. Berikut penerapannya.

> **Revisi 12 Agustus 2026.** Sebelumnya kaca dibatasi tiga panel per layar dan hanya dipakai di halaman publik. Sekarang kaca adalah bahasa visual **seluruh aplikasi** — termasuk panel admin, halaman masuk, dan halaman error. Yang menahan biayanya bukan lagi jumlah panelnya, tapi **besar blur-nya** (§5.1). Alasan perubahannya ada di §5.5.
>
> Ini membatalkan catatan lama di berkas panel admin yang menyebut panel "sengaja tidak memakai gaya kaca". Yang tetap berlaku dari catatan itu: **panel admin tidak ikut ruang kosong lega halaman publik.** Admin mengisi data berjam-jam di sana; kepadatan barisnya tetap rapat, hanya permukaannya yang berubah.

### 11.1 Lima tingkat kaca, dibedakan menurut peran

Satu opasitas untuk semua permukaan membuat tumpukan panel terlihat seperti satu bidang datar yang kotor. Permukaan yang saling menumpuk harus bisa dibedakan mata.

| Tingkat         | Opasitas | Blur     | Dipakai untuk                                                        |
| --------------- | -------- | -------- | -------------------------------------------------------------------- |
| `.glass-veil`   | 0,55     | 18px     | Bar navigasi, header panel, footer                                   |
| `.glass-card`   | 0,52     | **12px** | Kartu dalam grid — katalog, portfolio, anak usaha, kotak panel admin |
| `.glass-panel`  | 0,62     | 22px     | Permukaan utama — hero, kontak, tentang, kotak masuk                 |
| `.glass-raised` | 0,80     | 28px     | Yang menumpuk di atas konten lain — dialog, dropdown, sheet          |
| `.glass-field`  | 0,55     | —        | Kotak isian. Bayangan ke **dalam**, bukan melayang                   |

**Blur kartu grid sengaja lebih kecil** — 12px, dan turun lagi ke **8px di bawah 768px**. Di situlah biaya sebenarnya: dengan 20 kartu di layar, blur 22px terasa saat digulir di HP kelas bawah. Perbedaan 12px vs 22px nyaris tak terlihat pada kotak sekecil kartu, tapi bebannya jauh berbeda.

Semua nilai saturasi dinaikkan ke `1.5` — tanpa itu, warna gradasi yang lewat di belakang kaca terlihat pudar setelah di-blur.

### 11.2 Cadangan wajib

Setiap permukaan kaca **wajib** menuliskan warna latar padat lebih dulu, baru menambahkan blur di dalam `@supports`. Tanpa ini, browser lama merender panel transparan penuh dan teksnya tidak terbaca.

```css
.glass-panel {
    background: var(--glass-fallback); /* cadangan — ditulis LEBIH DULU */
    border: 1px solid var(--line-glass);
    box-shadow: var(--glass-shadow), var(--glass-highlight);
}

@supports (backdrop-filter: blur(1px)) or (-webkit-backdrop-filter: blur(1px)) {
    .glass-panel {
        background: rgba(255, 255, 255, 0.62);
        backdrop-filter: blur(22px) saturate(1.5);
        -webkit-backdrop-filter: blur(22px) saturate(1.5);
    }
}
```

Nilai kaca:

```css
--glass-panel-bg: rgba(255, 255, 255, 0.62);
--glass-card-bg: rgba(255, 255, 255, 0.52);
--glass-veil-bg: rgba(255, 255, 255, 0.55);
--glass-panel-blur: 22px;
--glass-card-blur: 12px; /* 8px di bawah 768px */
--glass-veil-blur: 18px;
--glass-saturate: 1.5;
--glass-fallback: #fdfbf6;
--glass-shadow:
    0 10px 34px rgba(90, 72, 40, 0.1), 0 2px 8px rgba(90, 72, 40, 0.05);
--glass-highlight: inset 0 1px 0 rgba(255, 255, 255, 0.75);
```

Opasitas boleh disetel antara **0,42–0,82** — seluruh rentang itu sudah diuji dan lolos AA di semua titik gradasi (§2.2).

**Bayangan kaca punya dua bagian:** bayangan jatuh yang lembut, **dan** garis sorot 1px di tepi atas (`--glass-highlight`). Garis sorot itu yang membuat permukaannya terbaca sebagai lembaran kaca, bukan kotak transparan. Jangan menghilangkannya.

### 11.3 Kontras diuji terhadap seluruh gradasi

Sudah dikerjakan — lihat §2.2. **Setiap warna teks baru wajib diuji terhadap seluruh titik gradasi pada tingkat kaca TERTIPIS yang akan memuatnya** (0,42), bukan terhadap putih. Menguji di atas putih saja akan meloloskan warna yang gagal di lapangan.

### 11.4 Mobile dirancang sendiri

Lihat §7. Mobile bukan hasil pengecilan desktop. Khusus kaca: blur kartu turun ke 8px di bawah 768px (§5.1).

### 11.5 Latar halaman wajib bergradasi

Kaca perlu sesuatu untuk dibiaskan. **Di atas latar putih polos, `backdrop-filter` tidak menghasilkan apa pun** — panelnya hanya terlihat seperti kotak abu-abu, dan seluruh efeknya sia-sia sambil tetap membayar biaya render.

Karena itu `<body>` membawa `--page-aurora`: empat titik gradasi di sudut, dipaku ke viewport dengan `background-attachment: fixed`. Kalau ikut menggulir, warna di belakang tiap panel berubah terus dan efeknya berkedip di layar panjang.

Konsekuensinya, **section tidak boleh punya latar padat sendiri.** Yang dulu `bg-white` sekarang transparan, dan panel kaca di dalamnyalah yang jadi permukaan bacanya. Satu blok padat di tengah halaman memotong aurora dan membuat temanya terbaca setengah jadi — itu juga alasan footer tidak lagi `bg-ink`.

### 11.6 Menghormati setelan sistem

Sebagian pengguna mematikan efek tembus pandang di tingkat OS karena membuat teks sulit dibaca. Di bawah `prefers-reduced-transparency: reduce`, **seluruh** permukaan jadi padat `--glass-fallback` dan blur dimatikan. Tata letaknya tidak berubah — hanya permukaannya.

---

## 12. Komponen

### Tombol

| Jenis               | Gaya                                                                                                    |
| ------------------- | ------------------------------------------------------------------------------------------------------- |
| Utama               | Latar `--gold-deep`, teks putih, `--r-full`, padding `14px 28px`. Hover → `--gold-hover`                |
| Sekunder            | Transparan, teks `--gold-deep`, garis 1px `--gold`, `--r-full`. Hover → latar `--gold-deep`, teks putih |
| Garis (panel admin) | `.glass-field` + garis `--line-strong`, teks `--ink`, `--r-full`. Hover → latar `--accent`              |
| Navigasi WA         | Sama seperti utama, padding lebih kecil `9px 18px`                                                      |
| Hapus               | Latar `--danger`, teks putih, `--r-full`                                                                |

Semua tombol dan tautan wajib punya **target sentuh minimal 44×44px** di mobile.

**Semua tombol berbentuk kapsul**, termasuk di panel admin. Tombol bersudut `rounded-md` adalah bawaan shadcn/ui yang dibiarkan apa adanya — §10 melarangnya.

### Kartu katalog

```
[gambar 4:3]
[kategori]          10,5px HURUF BESAR, --gold-deep
[nama]              Fraunces 600 17px
[deskripsi]         13,5px --ink-soft  (disembunyikan di mobile)
[harga] [tombol]    baris terpisah di mobile
```

Permukaan `.glass-card` (§5.1), sudut `--r-md`. Hover: naik 2px + bayangan `--glass-shadow`.

**Gambar gagal dimuat** → kotak `#F7F1E4` berisi inisial nama item, Fraunces 600 26px warna `--gold-deep` (spec §10). Bukan ikon rusak.

**Harga** → keterangan ("mulai dari") ditaruh di baris kecil **di atas** angkanya, bukan disambung sebaris — supaya angkanya tetap yang paling menonjol.

### Panel kaca

Sudut `--r-lg`, padding `--s6` (desktop) / `--s5 --s4` (mobile), garis `--line-glass`, bayangan `--glass-shadow` **beserta** sorot tepi `--glass-highlight` (§5.2).

### Kotak isian

`.glass-field` (§5.1), sudut `--r-sm`. Bedanya dari kartu: bayangannya mengarah ke **dalam**, sehingga permukaan yang bisa diketik terasa cekung di antara kartu yang melayang. Saat difokus, opasitasnya naik ke 0,78 — bukan berganti warna garis saja.

### Garis aksen

Batang 52×2px warna `--gold` di bawah judul hero. Elemen kecil yang mengikat halaman ke warna logo.

### Motif bintang

Titik-titik `radial-gradient` sangat samar (opasitas 0,18–0,30) di latar hero — benang merah dari taburan bintang di logo (spec §7 poin 4). **Syaratnya tidak boleh mengganggu keterbacaan**: jangan menaikkan opasitas, dan jangan menaruhnya di belakang blok teks yang padat.

---

## 13. Perilaku responsif

Titik ubah: **768px** (mobile ↔ desktop).

| Elemen             | Desktop                     | Mobile                                             |
| ------------------ | --------------------------- | -------------------------------------------------- |
| Navigasi           | Tautan mendatar + tombol WA | Logo + tombol menu; tautan jadi panel geser        |
| Grid katalog       | 3 kolom                     | 2 kolom                                            |
| Deskripsi kartu    | Tampil                      | Disembunyikan — di kolom sempit jadi terlalu padat |
| Baris harga+tombol | Sebaris                     | Bertumpuk, tombol selebar kartu                    |
| Bagian Tentang     | 2 kolom (teks + foto)       | 1 kolom, foto di bawah                             |
| Baris kontak       | 2 kolom                     | 1 kolom                                            |
| Tombol hero        | Selebar isinya              | **Selebar layar**                                  |
| Tombol WA melayang | Tidak ada                   | **Ada** — menempel di bawah saat digulir           |
| Judul hero         | 46px                        | 31px                                               |

**Tombol WhatsApp melayang hanya ada di mobile.** Mayoritas pengunjung datang dari tautan Instagram di HP, dan tujuan utamanya menghubungi lewat WA — tombol itu tidak boleh sampai hilang saat halaman digulir. Di desktop tidak perlu karena navigasi sudah menempel di atas.

---

## 14. Gambar

- Format **WebP** dengan cadangan PNG/JPEG
- Rasio: kartu katalog **4:3**, foto tentang **4:5**, portfolio **1:1**
- Semua gambar wajib punya `alt` yang bermakna — bukan "gambar" atau nama berkas
- Ukuran: web maksimal 1200px sisi terpanjang, thumbnail 400×400
- `loading="lazy"` untuk gambar di bawah lipatan layar

### Berkas logo Sweetness

Ada di `public/images/brand/`:

| Berkas                    | Ukuran | Penggunaan        |
| ------------------------- | ------ | ----------------- |
| `sweetness-logo-600.webp` | 600px  | Hero desktop      |
| `sweetness-logo-300.webp` | 300px  | Hero mobile, umum |
| `sweetness-badge-96.png`  | 96px   | Navigasi          |
| `sweetness-badge-48.png`  | 48px   | Favicon           |

Semuanya PNG/WebP latar transparan, sudah dibersihkan dari halo putih — aman ditempatkan di atas gradasi berwarna.

### Berkas logo J-Corporate Group

**Diganti 24 Agustus 2026** — client mengirim berkas baru menggantikan yang dipakai sejak 21 Agustus.

| Berkas                | Ukuran  | Penggunaan         |
| --------------------- | ------- | ------------------ |
| `jcorp-logo-600.webp` | 600×586 | Hero halaman induk |
| `jcorp-logo-300.webp` | 300px   | Umum, mobile       |
| `jcorp-badge-96.png`  | 96px    | Navigasi           |
| `jcorp-badge-48.png`  | 48px    | Favicon            |

**Nama berkasnya sengaja tidak berubah.** Logo lama ditimpa, bukan diberi nama baru — dengan begitu `ClientContent::JCORP_LOGO` dan penurunan jalur favicon tetap bekerja tanpa satu baris kode pun disunting.

Yang lama foto di atas kertas bertekstur; perlu penyaringan komponen tersambung untuk membuang bayangan pinggir foto, dan hasilnya tetap terbatas oleh mutu fotonya. Yang sekarang JPEG bersih 1064×1006 dengan latar putih rata (87,7% piksel di lum 240+) — pemisahan ambang saja sudah cukup.

**Warna tinta diseragamkan ke `rgb(34,34,34)`.** Sampel dari sumbernya menunjukkan rgb(15,15,15) sampai rgb(61,61,61) — semuanya abu-abu netral, jadi variasinya noise JPEG, bukan gradasi yang disengaja. Dibiarkan apa adanya, hasilnya 1.202 warna berbeda dan berkas WebP **56 KB**; setelah diseragamkan turun ke **33 KB**, tanpa perbedaan yang terlihat mata.

Kontras tinta di permukaan kartu: **15,12:1**.

Cara pembuatannya di [`docs/scripts/proses-logo-jcorp.php`](docs/scripts/proses-logo-jcorp.php).

### Berkas logo ngelash.id

Ditambahkan 22 Agustus 2026:

| Berkas                  | Ukuran  | Penggunaan        |
| ----------------------- | ------- | ----------------- |
| `ngelash-logo-600.webp` | 600×176 | Hero desktop      |
| `ngelash-logo-300.webp` | 300px   | Hero mobile, umum |
| `ngelash-badge-96.png`  | 96px    | Navigasi          |
| `ngelash-badge-48.png`  | 48px    | Favicon           |

**Rasionya sangat memanjang (kira-kira 3,4:1)** — berbeda dari dua logo lain yang nyaris bujur sangkar. Di lencana persegi ia hanya mengisi sepertiga tinggi kanvas; itu wajar, memotongnya jadi persegi akan memenggal hurufnya.

**Warnanya dibalik.** Sumbernya logo putih-emas di atas latar gelap `rgb(38,38,38)`. Di atas latar krem halaman, tulisan putih tidak terbaca sama sekali — jadi putihnya diganti tinta `rgb(38,36,42)`, sama dengan logo J-Corporate. **Aksen emasnya dipertahankan apa adanya** karena itu warna khas logonya dan kebetulan dekat dengan `--gold`.

Bentuk hurufnya tidak berubah sedikit pun; yang berubah hanya warnanya. Cara pembuatannya di [`docs/scripts/proses-logo-ngelash.php`](docs/scripts/proses-logo-ngelash.php).

> Versi asli berlatar gelap tetap sah dipakai di luar website — feed Instagram, kartu nama, spanduk. Yang dibalik hanya versi untuk halaman ini.

### Berkas logo Nail's by Me

Ditambahkan 22 Agustus 2026:

| Berkas                | Ukuran  | Penggunaan        |
| --------------------- | ------- | ----------------- |
| `nails-logo-600.webp` | 600×404 | Hero desktop      |
| `nails-logo-300.webp` | 300px   | Hero mobile, umum |
| `nails-badge-96.png`  | 96px    | Navigasi          |
| `nails-badge-48.png`  | 48px    | Favicon           |

**Warna aslinya dipertahankan** — hijau tua `rgb(42,65,4)`, satu-satunya logo yang bukan hitam/emas. Bedanya dari ngelash: yang itu berlatar gelap sehingga tulisan putihnya terpaksa dibalik; yang ini sudah gelap di atas terang, jadi kontrasnya aman apa adanya.

**Kontras diperiksa, bukan diasumsikan** (§5.3): **9,88:1** terhadap titik gradasi terpucat `#F5EFE3` — lolos AA dengan lega.

Ambang pemisahan latar sengaja longgar (235, bukan 210): di bawah tulisan utama ada baris teks kecil yang jauh lebih pucat. Dengan ambang ketat, baris itu terpotong separuh dan yang tersisa terlihat seperti noda.

Cara pembuatannya di [`docs/scripts/proses-logo-nails.php`](docs/scripts/proses-logo-nails.php).

### Berkas logo PT. Ayodya Utama Logistic

Ditambahkan 22 Agustus 2026:

| Berkas                 | Ukuran  | Penggunaan                           |
| ---------------------- | ------- | ------------------------------------ |
| `ayodya-logo-600.webp` | 600×109 | Hero desktop                         |
| `ayodya-logo-300.webp` | 300px   | Hero mobile, umum                    |
| `ayodya-badge-96.png`  | 96px    | Navigasi — **lambang bulatnya saja** |
| `ayodya-badge-48.png`  | 48px    | Favicon — **lambang bulatnya saja**  |

**Sumbernya kop surat 746×171, bukan logo bersih.** Isinya lambang bulat merah berlatar putih di kiri, lalu tulisan "PT. AYODYA UTAMA LOGISTIC" — **juga merah** — di atas latar biru muda bergradasi.

**Yang dibuang latar birunya, bukan setengah logonya.** Percobaan pertama memotong di x=138 karena cacah piksel biru melonjak di situ; itu salah baca — birunya memang mulai di sana, tapi itu latar di belakang tulisan, bukan penanda batas. Hasilnya lambang terpotong tanpa nama perusahaan, dan di halaman terlihat seperti gambar rusak.

Merah dan biru terpisah jelas (`rgb(228,68,68)` vs `rgb(147,197,240)`), jadi penyaringan warna bisa membuang latar tanpa menyentuh tulisannya.

**Pemisahan memakai keunggulan merah atas kanal lain, bukan luminansi.** `$lead = R - max(G, B)` — nilai ini yang sekaligus membedakan tulisan dari latar putih **dan** latar biru. Ambang luminansi memperlakukan semua warna sama dan membuat merahnya ikut pudar di tepi.

**Lencananya hanya lambang bulatnya.** Logo lengkap berasio 5,5:1; dimampatkan ke kotak 96×96, tulisannya menyusut jadi pita dua piksel yang tidak terbaca. Ini juga yang lazim — logo panjang untuk hero, lambang untuk ikon.

Kontras merah `rgb(227,43,43)` terhadap gradasi terpucat: **3,95:1** — lolos AA untuk **grafis besar**, tidak untuk teks. Cukup di sini karena dipakai sebagai gambar. Jangan pakai merah ini untuk tulisan.

Cara pembuatannya di [`docs/scripts/proses-logo-ayodya.php`](docs/scripts/proses-logo-ayodya.php).

### Berkas logo J-Land Property

Ditambahkan 23 Agustus 2026 — **yang terakhir dari enam**:

| Berkas                | Ukuran  | Penggunaan        |
| --------------------- | ------- | ----------------- |
| `jland-logo-600.webp` | 600×465 | Hero desktop      |
| `jland-logo-300.webp` | 300px   | Hero mobile, umum |
| `jland-badge-96.png`  | 96px    | Navigasi          |
| `jland-badge-48.png`  | 48px    | Favicon           |

**Yang paling bersih dari kelimanya.** Sumbernya 1254×1254 dengan latar putih rata (87,5% piksel di lum 240+) dan tinta `rgb(36,36,36)` (10,6% di lum 32–47) — tidak ada wilayah abu-abu di antaranya. Cukup pemisahan ambang: tanpa penyaringan komponen seperti Ayodya, tanpa pembalikan warna seperti ngelash.

**Sumbernya JPEG, bukan PNG,** dan itu berpengaruh. Kompresi JPEG meninggalkan riak halus di sekitar tepi tinta yang pada ambang ketat terbaca sebagai bintik. Karena itu `PAPER` dipasang agak tinggi (225) dengan tepi semi-transparan yang lebar — riaknya larut jadi gradasi. Hasilnya **nol bintik nyasar**, diperiksa dengan menghitung piksel pekat yang tetangganya semua transparan.

Kontras tinta di permukaan kartu: **14,75:1** — jauh melampaui AA.

Cara pembuatannya di [`docs/scripts/proses-logo-jland.php`](docs/scripts/proses-logo-jland.php).

> **Keenam entitas kini punya logo.** Rasionya berjauhan — Ayodya 5,50:1, J-Corporate 0,99:1 — dan kartu anak usaha menampungnya dengan area bertinggi tetap 68px (§4).

---

## 15. Nada tulisan

Bahasa Indonesia, **sopan tapi tidak kaku**. Menyebut pembaca "Anda".

Yang dihindari:

- Kalimat pemasaran kosong — "solusi terbaik untuk Anda", "kualitas premium terjamin"
- Huruf besar semua untuk penekanan
- Tanda seru berlebihan

Yang dicari: kalimat spesifik yang terasa ditulis orang sungguhan.

> Contoh dari mockup: _"Dibuat sedikit-sedikit tiap hari, supaya yang sampai ke meja Anda masih sehangat waktu keluar oven."_
> Bukan: _"Kue premium berkualitas tinggi dengan cita rasa terbaik!"_

**Semua teks di mockup adalah contoh, bukan final** — menunggu materi dari client.

---

## 16. Yang harus dihindari

Diperiksa ulang sebelum setiap deliverable:

- Hero rata tengah dengan gradasi tanpa identitas lain
- Tema bawaan shadcn/ui dibiarkan apa adanya
- Inter atau system-ui sebagai font utama
- Sudut, bayangan, dan jarak seragam di semua elemen tanpa hierarki
- Bola/blob 3D melayang, gradasi ungu-biru sebagai penanda "teknologi"
- Teks berkesan placeholder
- Komposisi terlalu simetris tanpa titik fokus
- **Kaca di atas latar putih polos** — tidak ada yang dibiaskan, biayanya tetap dibayar (§5.5)
- **Satu opasitas kaca untuk semua permukaan** — tumpukan panel jadi terlihat datar (§5.1)
- **Blok padat gelap** di halaman bertema kaca — memotong aurora dan membuat temanya terbaca setengah jadi

---

## 17. Untuk Fase 3

Token di §2, §3, dan §4 dituangkan ke `resources/css/app.css` sebagai CSS custom properties dengan nama yang sama persis, lalu dipetakan ke Tailwind lewat `@theme`.

Yang perlu diingat saat menulis kode:

1. **Jangan** menaruh `dark:` di komponen baru — project ini bertema terang saja. Baris `@custom-variant dark` di `app.css` wajib dipertahankan (alasannya ada di komentar berkas itu).
2. Cadangan permukaan kaca ditulis **sebelum** blok `@supports`, selalu.
3. Warna teks emas selalu `--gold-deep`, tidak pernah `--gold`. Di atas kaca, `--gold-mid` pun hanya untuk judul ≥24px — lihat peringatan di §2.2.
4. Pilih tingkat kaca menurut **peran** permukaannya (§5.1), jangan memakai `.glass-panel` untuk semuanya.
5. Section **tidak** membawa latar padat sendiri — aurora di `<body>` yang jadi latarnya (§5.5).
6. Mobile dikerjakan sebagai pass tersendiri, bukan hasil pengecilan.

### Menempelkan kaca ke komponen shadcn/ui

Blur untuk komponen shadcn/ui dipasang di `app.css` lewat selector `[data-slot='…']`, **bukan** dengan menyunting satu per satu berkas komponennya. Warna permukaannya sudah tembus pandang dari token di `:root`.

Alasannya: komponen di `resources/js/components/ui/` disalin dari upstream dan sesekali perlu disalin ulang. Kalau blur ditulis di dalam berkasnya, setiap penyalinan ulang menghapus temanya diam-diam. Dengan cara ini, komponen boleh ditimpa kapan saja dan tetap ikut tema.

Pengecualiannya yang disengaja: **tooltip tetap padat emas.** Kotaknya terlalu kecil dan terlalu sebentar tampil untuk dibaca kalau tembus pandang.
