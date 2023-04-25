<?php

namespace App\Http\Requests;

use App\Models\RepaymentCycle;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        // $id = self::getCustomRepaymentCycleId();
        return [
            'customer_id' => ['required', 'exists:customers,id'],
            'repayment' => ['required', 'numeric'],
            'repayment_duration_id' => ['required', 'exists:repayment_durations,id'],
            'repayment_cycle_id' => ['required', 'exists:repayment_cycles,id'],
            'down_payment' => ['required', 'numeric'],
            'product_price' => ['required', 'numeric'],
            'cost_price' => ['required', 'numeric'],
            'product_name' => ['required', 'string'],
            'down_payment_rate_id' => ['sometimes', 'integer', 'exists:down_payment_rates,id'],
            'guarantors' => ['sometimes', 'array', 'min:1'],
            'guarantors.*.first_name' => ['required', 'string', 'max:200'],
            'guarantors.*.last_name' => ['required', 'string', 'max:200'],
            'guarantors.*.email' => ['sometimes', 'email', 'max:200'],
            'guarantors.*.home_address' => ['required', 'string', 'max:200'],
            'guarantors.*.work_address' => ['sometimes', 'string', 'max:200'],
            'guarantors.*.phone_number' => ['required', 'string', 'max:14'],
            'guarantors.*.gender' => ['sometimes', 'string', Rule::in(['male', 'female'])],
            'guarantors.*.relationship' => ['sometimes', 'string'],
            'guarantors.*.occupation' => ['sometimes', 'string'],
            'documents' =>  ['sometimes', 'array', 'min:1'],
            'documents.*.url' => ['required', 'string'],
            'documents.*.name' => ['required', 'string'],
            'has_document' => ['sometimes', 'string'],
        ];
    }

    private static function getCustomRepaymentCycleId()
    {
        return RepaymentCycle::where('name', RepaymentCycle::CUSTOM)->first()->id;
    }
}
