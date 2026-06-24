<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['from_location', 'to_location', 'fare_kes', 'departure_time', 'arrival_time', 'duration', 'is_active'])]
class Route extends Model
{
    protected function casts(): array
    {
        return [
            'fare_kes' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }
}
