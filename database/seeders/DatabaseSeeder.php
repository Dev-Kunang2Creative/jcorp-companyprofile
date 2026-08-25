<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seeder tidak membuat akun admin apa pun.
     *
     * Akun dengan password yang bisa ditebak ("password") berbahaya kalau
     * seeder ikut jalan di server sungguhan. Akun dibuat lewat:
     *
     *     php artisan jcorp:make-admin
     *
     * yang meminta password dalam mode tersembunyi.
     */
    public function run(): void
    {
        $this->call(BusinessSeeder::class);

        // Materi asli dari client. Aman ikut jalan di server: isinya bukan
        // karangan, dan kolom yang sudah disunting admin tidak ditimpa.
        //
        // SampleContentSeeder TIDAK dipanggil di sini — isinya karangan, dan
        // memasukkannya ke server yang sudah tayang berarti data palsu ikut
        // terbaca pengunjung. Dijalankan terpisah saat dibutuhkan:
        //
        //     php artisan db:seed --class=SampleContentSeeder
        $this->call(ClientContentSeeder::class);
    }
}
