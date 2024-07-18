<?php

namespace App\Http\Requests\Longinfo;

use Illuminate\Foundation\Http\FormRequest;

class SelectRequest extends FormRequest
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
            //'検証する値'=>['検証ルール1', '検証ルール2']
            'factory' =>['required'],
            'department' =>['required'],
            'line' =>['required'],
            'numbers' =>['required'],
            'workers' =>['required'],

        ];
    }
    // バリデーションに引っかかったときに出す文字
    public function messages()
    {
        return [
            'factory.required' =>'工場を選択してください',
            'department.required' =>'製造課を選択してください',
            'line.required' =>'W/Cを選択してください',
            'numbers.required' =>'番号を入力してください',
            'workers.required' =>'作業者を入力してください',
        ];
    }
}
