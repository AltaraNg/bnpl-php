<?php

namespace App\Repositories\Eloquent\Repository;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Model;

class CustomerRepository extends BaseRepository
{
    public function __construct(Customer $model)
    {
        parent::__construct($model);
    }

    public function findByEmail(string $email): ?Model
    {
        return $this->model->query()->where('email', $email)->first();
    }
    public function findByTelephone(string $telephone): ?Model
    {
        return $this->model->query()->with(['orders' => function ($query) {
            $query->where('bnpl_vendor_product_id', '<>', null);
        }, 'orders.amortizations', 'orders.bnplProduct', 'orders.vendor', 'orders.branch'])->where('telephone', $telephone)->first();
    }

    public function customers(int $vendor_id)
    {
        return $this->model::query()->when(request('telephone'), function ($query) {
            $query->where('telephone', 'LIKE', '%' . request('telephone') . '%');
        })->with('orders')->latest('created_at')->when(request('telephone') == null, function ($query) use ($vendor_id) {
            $query->where('user_id', $vendor_id)->orWhereHas('bnplCustomers', function ($query) use ($vendor_id)
            {
                $query->where('user_id', $vendor_id);
            });
        })->paginate(request('per_page'));
    }
    public function filter()
    {
    }
}
