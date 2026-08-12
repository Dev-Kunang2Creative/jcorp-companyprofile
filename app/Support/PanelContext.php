<?php

namespace App\Support;

/**
 * Nilai bersama untuk konteks panel admin.
 *
 * Kunci sesinya dipakai di dua tempat — trait ResolvesActiveBusiness yang
 * menyimpannya, dan HandleInertiaRequests yang membacanya untuk menentukan
 * menu sidebar. Ditaruh di kelas tersendiri, bukan di trait, karena konstanta
 * pada trait tidak bisa diakses lewat namanya.
 */
final class PanelContext
{
    /**
     * Kunci sesi tempat anak usaha pilihan super-admin disimpan.
     *
     * Business_admin tidak pernah menulis ke sini — konteksnya selalu
     * ditentukan dari `business_id` miliknya sendiri.
     */
    public const SESSION_KEY = 'panel.business';
}
