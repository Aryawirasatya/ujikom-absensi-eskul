<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PointRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'rule_name',
        'target_role',
        'condition_field',
        'condition_operator',
        'condition_value',
        'condition_value_2',
        'point_modifier',
    ];
}