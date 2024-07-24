<?php

namespace App\Http\Requests\Masta;

use Illuminate\Foundation\Http\FormRequest;

class ShippingUploadRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'shipment_file' => ['required', 'mimes:xls,xlsx'],
            'delivery_day' => ['required', 'date'],
            'delivery_day_end' => ['required', 'date', 'after_or_equal:delivery_day']
        ];
    }
    // バリデーションに引っかかったときに出す文字
    public function messages()
    {
        return [
            'shipment_file.required' => 'ファイルが選択されていないか、ファイル形式が違います。確認してください。',
            'delivery_day.required' => '開始日を入力してください。',
            'delivery_day.date' => '開始日は有効な日付を入力してください。',
            'delivery_day_end.required' => '終了日を入力してください。',
            'delivery_day_end.date' => '終了日は有効な日付を入力してください。',
            'delivery_day_end.after_or_equal' => '終了日は開始日以降の日付を入力してください。',
        ];
    }
}
