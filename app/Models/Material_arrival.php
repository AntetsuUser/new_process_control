<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Material_arrival extends Model
{
    use HasFactory;

    protected $table = 'material_arrival';

    protected $fillable = [
        'arrival_date',
        'item_code',
        'quantity',
        'note',
        'is_matched',
        'status',
        'history_id',
    ];
}
