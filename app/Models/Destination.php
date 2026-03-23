<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Destination extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    /**
     * Get the flights for the destination.
     */
    public function flights()
    {
        return $this->hasMany(Flight::class);
    }
}