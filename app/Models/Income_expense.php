<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Income_expense extends Model
{
    protected $fillable = ['type', 'category', 'total', 'keterangan', 'tanggal'];
}
