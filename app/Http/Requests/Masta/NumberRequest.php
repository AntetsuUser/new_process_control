<?php

namespace App\Http\Requests\Masta;

use Illuminate\Foundation\Http\FormRequest;

class NumberRequest extends FormRequest
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
            // //図面番号
            // 'print_number_0' =>['required'],
            // 'print_number_1' =>['required'],
            // 'print_number_2' =>['required'],
            // //工場
            // 'factories_id[0]' =>['required'],
            // 'factories_id_1' => ['required'],
            // 'factories_id_2' => ['required'],
            // //製造課
            // 'departments_id_0' =>['required'],
            // 'departments_id_1' =>['required'],
            // 'departments_id_2' =>['required'],

        ];
    }
    // バリデーションに引っかかったときに出す文字
    public function messages()
    {
        return [
            // 'print_number_0.required' =>'図面番号を入力してください',
            // 'print_number_1.required' =>'図面番号を入力してください',
            // 'print_number_2.required' =>'図面番号を入力してください',

            'factories_id[0].required' => ' 工場を選択してください',  
            // 'factories_id_1.required' => ' 工場を選択してください',  
            // 'factories_id_2.required' => ' 工場を選択してください',  

            // 'departments_id_0.required' => ' 製造課を選択してください',
            // 'departments_id_1.required' => ' 製造課を選択してください',
            // 'departments_id_2.required' => ' 製造課を選択してください',


        ];
    }
}
