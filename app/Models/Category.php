<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'category';
    protected $fillable = ['parent_id', 'name'];

    public function parent()
    {
        return $this->belongsTo('App\Models\Category','parent_id')->where('parent_id',null);
    }

    public function children()
    {
        return $this->hasMany('App\Models\Category','parent_id');
    }
}
