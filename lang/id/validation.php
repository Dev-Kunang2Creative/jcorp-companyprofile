<?php

/**
 * Pesan validasi Bahasa Indonesia.
 *
 * Laravel hanya membawa terjemahan `en`. Project ini disetel APP_LOCALE=id
 * DAN APP_FALLBACK_LOCALE=id, jadi tidak ada tempat mundur — tanpa berkas
 * ini, yang muncul di layar adalah kunci mentahnya:
 *
 *     validation.password.mixed
 *     validation.required
 *
 * Ketahuan saat membuat akun admin di server, bukan dari test — test hanya
 * memeriksa ADA tidaknya error, bukan bunyi pesannya.
 *
 * Isinya sengaja tidak lengkap: hanya aturan yang dipakai project ini plus
 * beberapa yang mungkin muncul. Menyalin seluruh berkas Laravel berarti
 * memelihara ratusan baris yang tidak pernah terpakai.
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Aturan yang dipakai project ini
    |--------------------------------------------------------------------------
    */

    'required' => 'Kolom :attribute wajib diisi.',
    'string' => 'Kolom :attribute harus berupa teks.',
    'boolean' => 'Kolom :attribute harus bernilai ya atau tidak.',
    'integer' => 'Kolom :attribute harus berupa angka bulat.',
    'numeric' => 'Kolom :attribute harus berupa angka.',
    'email' => 'Kolom :attribute harus berupa alamat email yang benar.',
    'unique' => ':attribute ini sudah dipakai.',
    'exists' => ':attribute yang dipilih tidak ditemukan.',
    'regex' => 'Format :attribute tidak sesuai.',
    'confirmed' => 'Konfirmasi :attribute tidak cocok.',
    'current_password' => 'Password yang Anda masukkan salah.',
    'prohibited' => 'Kolom :attribute tidak boleh diisi.',
    'enum' => ':attribute yang dipilih tidak sah.',
    'in' => ':attribute yang dipilih tidak sah.',
    'not_in' => ':attribute yang dipilih tidak sah.',
    'accepted' => 'Kolom :attribute harus disetujui.',
    'filled' => 'Kolom :attribute tidak boleh kosong.',
    'present' => 'Kolom :attribute harus disertakan.',
    'array' => 'Kolom :attribute harus berupa daftar.',
    'date' => 'Kolom :attribute harus berupa tanggal yang benar.',
    'same' => 'Kolom :attribute harus sama dengan :other.',
    'different' => 'Kolom :attribute dan :other harus berbeda.',
    'digits' => 'Kolom :attribute harus terdiri dari :digits angka.',

    /*
    |--------------------------------------------------------------------------
    | Unggahan berkas
    |--------------------------------------------------------------------------
    */

    'file' => 'Kolom :attribute harus berupa berkas.',
    'image' => 'Berkas :attribute harus berupa gambar.',
    'mimes' => 'Berkas :attribute harus berformat: :values.',
    'mimetypes' => 'Berkas :attribute harus berformat: :values.',
    'extensions' => 'Berkas :attribute harus berakhiran: :values.',
    'uploaded' => 'Berkas :attribute gagal diunggah. Kemungkinan ukurannya melebihi batas server.',

    /*
    |--------------------------------------------------------------------------
    | Batas ukuran — bentuk pesannya berbeda tergantung jenis kolomnya
    |--------------------------------------------------------------------------
    */

    'max' => [
        'array' => 'Kolom :attribute tidak boleh lebih dari :max item.',
        'file' => 'Berkas :attribute tidak boleh lebih dari :max KB.',
        'numeric' => 'Kolom :attribute tidak boleh lebih dari :max.',
        'string' => 'Kolom :attribute tidak boleh lebih dari :max karakter.',
    ],

    'min' => [
        'array' => 'Kolom :attribute minimal berisi :min item.',
        'file' => 'Berkas :attribute minimal berukuran :min KB.',
        'numeric' => 'Kolom :attribute minimal :min.',
        'string' => 'Kolom :attribute minimal :min karakter.',
    ],

    'gt' => [
        'array' => 'Kolom :attribute harus berisi lebih dari :value item.',
        'file' => 'Berkas :attribute harus lebih besar dari :value KB.',
        'numeric' => 'Kolom :attribute harus lebih besar dari :value.',
        'string' => 'Kolom :attribute harus lebih dari :value karakter.',
    ],

    'lt' => [
        'array' => 'Kolom :attribute harus berisi kurang dari :value item.',
        'file' => 'Berkas :attribute harus lebih kecil dari :value KB.',
        'numeric' => 'Kolom :attribute harus lebih kecil dari :value.',
        'string' => 'Kolom :attribute harus kurang dari :value karakter.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Syarat password
    |--------------------------------------------------------------------------
    |
    | Inilah yang memunculkan "validation.password.mixed" saat membuat akun
    | admin di server. Aturannya diatur di AppServiceProvider: di produksi
    | password wajib 12 karakter dengan huruf besar-kecil, angka, dan simbol.
    |
    */

    'password' => [
        'letters' => 'Password harus memuat setidaknya satu huruf.',
        'mixed' => 'Password harus memuat huruf besar dan huruf kecil.',
        'numbers' => 'Password harus memuat setidaknya satu angka.',
        'symbols' => 'Password harus memuat setidaknya satu simbol, misalnya ! @ # $.',
        'uncompromised' => 'Password ini pernah muncul dalam kebocoran data. Pilih password lain.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Nama kolom
    |--------------------------------------------------------------------------
    |
    | Mengganti nama teknis kolom dengan sebutan yang dimengerti admin —
    | "Kolom nama item wajib diisi", bukan "Kolom name wajib diisi".
    |
    | Sebagian FormRequest sudah punya attributes() sendiri yang lebih
    | spesifik; yang di sini jadi cadangan untuk kolom yang belum diatur.
    |
    */

    'attributes' => [
        'name' => 'nama',
        'email' => 'email',
        'password' => 'password',
        'password_confirmation' => 'konfirmasi password',
        'current_password' => 'password saat ini',
        'description' => 'deskripsi',
        'price' => 'harga',
        'price_note' => 'satuan harga',
        'category' => 'kategori',
        'sort_order' => 'urutan tampil',
        'is_available' => 'status tampil',
        'is_published' => 'status terbit',
        'has_portfolio' => 'sakelar portfolio',
        'image' => 'foto',
        'caption' => 'keterangan',
        'whatsapp' => 'nomor WhatsApp',
        'instagram' => 'username Instagram',
        'tiktok' => 'username TikTok',
        'address' => 'alamat',
        'business_hours' => 'jam buka',
        'catalog_label' => 'label katalog',
        'portfolio_label' => 'label portfolio',
        'tagline' => 'tagline',
        'business' => 'anak usaha',
        'role' => 'peran',
    ],

    /*
    |--------------------------------------------------------------------------
    | Pesan khusus per kolom
    |--------------------------------------------------------------------------
    */

    'custom' => [
        'password' => [
            'min' => [
                'string' => 'Password minimal :min karakter.',
            ],
        ],
    ],

];
