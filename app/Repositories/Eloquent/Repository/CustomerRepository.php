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
        return $this->model->query()->where('telephone', $telephone)->first();
    }

    public function filter()
    {

    }
}

