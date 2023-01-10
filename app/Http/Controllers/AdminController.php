<?php

namespace App\Http\Controllers;

use App\Events\VendorRegisteredEvent;
use App\Http\Requests\VendorRequest;
use App\Models\Branch;
use App\Models\Otp;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class AdminController extends Controller
{

    /**
     * @throws \Exception
     */
    public function createVendor(VendorRequest $request): JsonResponse
    {
        $role = Role::query()->where('name', 'Vendor')->where('guard_name', 'sanctum')->first();
        $password = Str::random(4);
        $branchID = Branch::query()->where('name', 'like', '%Ikoyi%')->first()->id;
        $otp =  $this->generateNumericOTP(6);
        $data = [
            'full_name' => $request->full_name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'address' => $request->address,
            'gender' => $request->gender,
            'staff_id' => 'VD/CT/' . Carbon::now()->year . '/' . User::getNextModelId(), //VD => vendor CT =>customer
            'date_of_appointment' => Carbon::now()->toDateString(),
            'branch_id' => $branchID,
            'api_token' => $otp,
            'password' => Hash::make($password),
        ];
        $vendor = User::query()->create($data);
        $vendor->assignRole($role);
        $data = [
            'otp' =>  $otp,
            'username' => $vendor->phone_number,
        ];
        
        $query  = Arr::query($data);
        $url = env('FRONTEND_APP_URL'). $query;
        dd($url);
        event(new VendorRegisteredEvent($vendor, $otp));
        return $this->respondSuccess(['vendor' => $vendor], 'Vendor created successfully');
    }


    public function updateVendor(VendorRequest $request, User $vendor): JsonResponse
    {

        $vendor->update($request->validated());
        return $this->respondSuccess(['vendor' => $vendor->refresh()], 'Vendor updated successfully');
    }

    public function viewVendor(User $vendor)
    {
        return $this->respondSuccess(['vendor' => $vendor], 'Vendor Loaded successfully');
    }
    public function deactivateVendor(User $vendor): JsonResponse
    {
        $vendor->portal_access = 0;
        $vendor->save();
        return $this->respondSuccess(['vendor' => $vendor->refresh()], 'Vendor deactivated successfully');
    }
    // Function to generate OTP
    protected function generateNumericOTP(int $n): string
    {

        // Take a generator string which consist of
        // all numeric digits
        $generator = "1357902468";

        // Iterate for n-times and pick a single character
        // from generator and append it to $result

        // Logic for generating a random character from generator
        //     ---generate a random number
        //     ---take modulus of same with length of generator (say i)
        //     ---append the character at place (i) from generator to result
        $result = "";
        for ($i = 1; $i <= $n; $i++) {
            $result .= substr($generator, (rand() % (strlen($generator))), 1);
        }
        // Return result
        return $result;
    }
}
