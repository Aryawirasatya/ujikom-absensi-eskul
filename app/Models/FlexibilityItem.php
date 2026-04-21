<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class FlexibilityItem extends Model
{
    use SoftDeletes;
    use HasFactory;

    protected $fillable = [
    'item_name',
    'token_type',
    'effect_value',  
    'point_cost',
    'stock_limit',
];
public function tokens()
{
    return $this->hasMany(UserToken::class, 'item_id');
}
    // Relasi: Satu jenis item bisa dimiliki oleh banyak user (dalam bentuk token)
    public function userTokens()
    {
        return $this->hasMany(UserToken::class, 'item_id');
    }
}