<?php

namespace App\DataTransferObject;

use App\Http\Requests\OrderRequest;

class GuarantorDto
{
    public  string $email;
    public  string $work_address;
    public  string $gender;
    public  string $relationship;
    public  string $occupation;
    public function __construct(
        public readonly int $customer_id,
        public readonly string $first_name,
        public readonly string $last_name,
        public readonly string $phone_number,
        public readonly string $home_address
    ) {
    }
    static public function fromOrderApiRequest(OrderRequest $request): array
    {
        $data = [];
        foreach ($request->guarantors as $key => $value) {
            $data[] = new self(
                $request->input('customer_id'),
                $value['first_name'],
                $value['last_name'],
                $value['phone_number'],
                $value['home_address'],
            );
        }
        return $data;
    }
    static public function fromSelf(GuarantorDto $dto): self
    {
        return new self(
            $dto->customer_id,
            $dto->first_name,
            $dto->last_name,
            $dto->phone_number,
            $dto->phone_number
        );
    }
}
