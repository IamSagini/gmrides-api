<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Destination extends Model
{
    protected $fillable = [
        'name', 'location', 'from_location', 'to_location',
        'description', 'image_url', 'tag',
    ];
}
