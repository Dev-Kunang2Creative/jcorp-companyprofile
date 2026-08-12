<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

abstract class Controller
{
    // Sejak Laravel 11 trait ini tidak lagi terpasang otomatis. Dipasang di
    // sini karena pengecekan kepemilikan lewat authorize() adalah syarat yang
    // tidak boleh dilanggar di project ini (spec §6).
    use AuthorizesRequests;
}
