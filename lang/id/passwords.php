<?php

/**
 * Pesan reset password Bahasa Indonesia.
 *
 * Catatan: fitur reset password belum benar-benar berfungsi di server karena
 * MAIL_MAILER=log — emailnya hanya dicatat ke berkas log, tidak terkirim.
 * Pesan-pesan ini tetap diterjemahkan supaya begitu SMTP dipasang, alurnya
 * langsung berbahasa Indonesia tanpa perlu diingat lagi.
 *
 * `sent` dan `user` sengaja berbunyi sama. Membedakan keduanya memberi tahu
 * penyerang apakah sebuah email terdaftar di sistem — jadi Laravel memang
 * menyarankan jawaban yang seragam.
 */

return [

    'reset' => 'Password Anda sudah diganti.',
    'sent' => 'Kalau email itu terdaftar, tautan penggantian password sudah kami kirim.',
    'throttled' => 'Mohon tunggu sebentar sebelum mencoba lagi.',
    'token' => 'Tautan penggantian password ini tidak sah atau sudah kedaluwarsa.',
    'user' => 'Kalau email itu terdaftar, tautan penggantian password sudah kami kirim.',

];
