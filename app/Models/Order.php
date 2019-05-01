<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{

    // status list
    const STATUS_PENDING = 0;
    const STATUS_CANCELED = 1;
    const STATUS_FAILED = 2;
    const STATUS_COMPLETE = 3;

    //
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $table = 'order';

    protected $fillable = ['user_id', 'amount', 'status', 'invoice_number'];

    public function items()
    {
        return $this->hasMany('App\Models\OrderItem','order_id');
    }
}
