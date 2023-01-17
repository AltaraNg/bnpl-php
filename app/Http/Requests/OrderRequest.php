<?php

namespace App\Http\Requests;

use App\Models\RepaymentCycle;
use Illuminate\Foundation\Http\FormRequest;

class OrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $id = self::getCustomRepaymentCycleId();
        return [
            'customer_id' => ['required', 'exists:customers,id'],
            'inventory_id' => ['required', 'exists:inventories,id'],
            'bank_id' => ['required', 'exists:banks,id'],
            'repayment' => ['required', 'numeric'],
            'owner_id' => ['required', 'exists:users,id'],
            'serial_number' => ['sometimes', 'string'],
            'sales_category_id' => ['required', 'exists:sales_categories,id'],
            'repayment_duration_id' => ['required', 'exists:repayment_durations,id'],
            'repayment_cycle_id' => ['required', 'exists:repayment_cycles,id'],
            'business_type_id' => ['required', 'exists:business_types,id'],
            'payment_method_id' => ['required', 'exists:payment_methods,id'],
            'down_payment' => ['required', 'numeric'],
            'discount' => ['sometimes', 'array'],
            'discount.*' => ['sometimes', 'numeric', 'exists:discounts,id'],
            'product_price' => ['required', 'numeric'],
            'custom_date' => ['integer', 'min:1', 'max:31', 'required_if:repayment_cycle_id,' . $id],
            'down_payment_rate_id' => ['sometimes', 'exists:down_payment_rates,id'],
            'order_type_id' => ['sometimes', 'exists:order_types,id'],
            'payment_gateway_id' => ['sometimes', 'exists:payment_gateways,id'],
            'discount_id' => ['sometimes', 'exists:discounts,id'],
        ];
    }

    private static function getCustomRepaymentCycleId()
    {
        return RepaymentCycle::where('name', RepaymentCycle::CUSTOM)->first()->id;
    }
}
