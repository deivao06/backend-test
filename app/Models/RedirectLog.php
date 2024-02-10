<?php

namespace App\Models;

use Database\Factories\RedirectLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RedirectLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'redirect_id',
        'ip',
        'user_agent',
        'header_referer',
        'query_params'
    ];

    protected static function newFactory()
    {
        return RedirectLogFactory::new();
    }
}
