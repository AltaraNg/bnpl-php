<?php

namespace App\Http\Controllers;

use App\Http\Resources\CustomerResource;
use App\Repositories\Eloquent\Repository\CustomerRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function __construct(private readonly CustomerRepository $customerRepository)
    {
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $customers = $this->customerRepository->customers($user->id);
        return $this->respondSuccess(['customers' => $customers], 'Vendor customers fetched');
    }

    public function show(Request $request, string $telephone)
    {
        $customer  = $this->customerRepository->findByTelephone($telephone);
        if (!$customer) {
            return $this->respondNotFound('Customer not found');
        }
        return $this->respondWithResource(new CustomerResource($customer), 'Fetch customer successfully');
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'first_name' => ['required', 'string', 'max:200'],
            'last_name' => ['required', 'string', 'max:200'],
            'email' => ['sometimes', 'email', 'max:100', 'unique:customers,email'],
            'telephone' => ['required', 'string', 'max:11', 'unique:customers,telephone'],
            'home_address' => ['required', 'string', 'max:200'],
            'date_of_birth' => ['required', 'date'],
            'state' => ['required', 'string'],
            'city' => ['required', 'string'],
        ]);
        $data = [
            'first_name' => $request->input('first_name'),
            'last_name' => $request->input('last_name'),
            'email' => $request->input('email'),
            'telephone' => $request->input('telephone'),
            'area_address' => $request->input('home_address'),
            'date_of_birth' => $request->input('date_of_birth'),
            'user_id' => $request->user()->id,
            'state' => $request->input('state'),
            'city' => $request->input('city'),
            ...$this->setNotNullableFields(),
        ];
        $customer = $this->customerRepository->create($data);
        return $this->respondWithResource(new CustomerResource($customer), 'Customer created successfully');
    }



    private function setNotNullableFields()
    {
        return [
            'registration_channel' => 'bnpl',
            'first_name' => 'N/A',
            'last_name' => 'N/A',
            'on_boarded' => false,
            'add_street' => 'N/A',
            'employee_name' => 'bnpl',
            'employee_id' => 1,
            'date_of_registration' => Carbon::now(),
            'add_nbstop' => 'N/A',
            'add_houseno' => 'N/A',
            'city' => 'N/A',
            'state' => 'N/A',
            'gender' => 'N/A',
            'date_of_birth' => 'N/A',
            'civil_status' => 'N/A',
            'type_of_home' => 'N/A',
            'no_of_rooms' => 'N/A',
            'duration_of_residence' => 0,
            'people_in_household' => 0,
            'number_of_work' => 0,
            'depend_on_you' => 0,
            'level_of_education' => 'N/A',
            'visit_hour_from' => 'N/A',
            'visit_hour_to' => 'N/A',
            'employment_status' => 'N/A',
            'name_of_company_or_business' => 'N/A',
            'cadd_nbstop' => 'N/A',
            'company_city' => 'N/A',
            'company_state' => 'N/A',
            'company_telno' => 'N/A',
            'days_of_work' => 'N/A',
            'comp_street_name' => 'N/A',
            'comp_house_no' => 'N/A',
            'comp_area' => 'N/A',
            'current_sal_or_business_income' => 'N/A',
            'cvisit_hour_from' => 'N/A',
            'cvisit_hour_to' => 'N/A',
            'nextofkin_first_name' => 'N/A',
            'nextofkin_middle_name'  => 'N/A',
            'nextofkin_last_name' => 'N/A',
            'nextofkin_telno' => 'N/A'
        ];
    }
}
