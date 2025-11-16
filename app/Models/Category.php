<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    /**
     * The attributes that are mass assignable
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
    ];

    /**
     * The attributes that are not mass assignable
     *
     * @var array<int, string>
     */
    protected $guarded = [
        'slug',
    ];

    /**
     * Get the products that belong to the category
     */
    public function products()
    {
        return $this->belongsToMany(Product::class)->withTimestamps();
    }

    /**
     * Get the route key for the model
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
