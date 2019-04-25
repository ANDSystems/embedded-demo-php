<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'product';
    protected $fillable = ['category_id', 'subcategory_id', 'name', 'description', 'price', 'image'];
}
