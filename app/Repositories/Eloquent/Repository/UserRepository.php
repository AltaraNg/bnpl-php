<?php

namespace App\Repositories\Eloquent\Repository;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class UserRepository extends BaseRepository
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function findByEmail(string $email): ?Model
    {
        return $this->model->query()->where('email', $email)->first();
    }

    public function filter()
    {

        //TODO, load role relation
        return $this->model->query()->with(
            [
                'branch:id,name,status,category,description',
                'roles:id,name',
            ]
        )->whereName(request('name'))
            ->whereLocation(request('location'))
            ->whereEmail(request('email'))
            ->whereRole(request('role'));
    }
}

