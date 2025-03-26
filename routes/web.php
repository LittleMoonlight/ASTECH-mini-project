<?php

use App\Http\Controllers\Income_expense;
use Illuminate\Support\Facades\Route;

Route::get('/', [Income_expense::class, 'index'])->name('welcome');

Route::post('/simpan_data', [Income_expense::class, 'simpan'])->name('simpan_data');
