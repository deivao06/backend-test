<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Redirect extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'url',
    ];

    protected function queryParams(): Attribute
    {
        return Attribute::make(
            get: function($value) {
                $queryParams = [];
                $parsedUrl = parse_url($this->url);

                if(isset($parsedUrl['query'])) {
                    parse_str($parsedUrl['query'], $queryParams);
                }
                
                return $queryParams;
            }
        );
    }
}
