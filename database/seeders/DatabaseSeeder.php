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
    }
}
