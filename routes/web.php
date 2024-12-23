<?php

use App\Livewire\Home\Streamers;
use Illuminate\Support\Facades\Route;

Route::get('/', Streamers::class)->name('streamers.home');
