<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Specification extends Model
{
    protected $fillable = [
        'specification_group_id',
        'name',
        'data_type',
        'unit'
    ];

    public function group()
    {
        return $this->belongsTo(SpecificationGroup::class, 'specification_group_id');
    }

    public function products()
    {
        return $this->hasMany(ProductSpecification::class);
    }

    public function scoreConfig()
    {
        return $this->hasOne(SpecificationScore::class);
    }
}
