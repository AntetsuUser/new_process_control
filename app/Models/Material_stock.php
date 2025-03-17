<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Material_stock extends Model
{
    use HasFactory;

    protected $table = 'material_stock';

    protected $fillable = [
        'material_name',
        'material_stock',
        'material_for_mark',
        'before',
        'using_name',
    ];
}
