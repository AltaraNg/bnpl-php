<?php

namespace App\Repositories\Eloquent\Repository;

use App\Models\Order;
use Illuminate\Database\Eloquent\Model;

class OrderRepository extends BaseRepository
{
    public function __construct(Order $model)
    {
        parent::__construct($model);
    }



    public function myOrders(int $owner_id)
    {
        return $this->model::query()->with('customer')->where('user_id', $owner_id)->paginate(request('per_page'));
    }
}
