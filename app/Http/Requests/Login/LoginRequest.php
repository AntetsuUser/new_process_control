<?php

namespace App\Http\Requests\Login;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
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
           'name' => ['required', 'regex:/^[a-zA-Z0-9_-]+$/'], // 英数字とアンダースコアのみ
            'password' =>['required'],
            'all_departments_id' =>['required'],
            'positions_id' =>['required'],
        ];
    }
    // バリデーションに引っかかったときに出す文字
    public function messages()
    {
        return [
            'name.required' =>'ユーザ名を入力してください',
            'name.regex' => ':attribute には英数字とアンダースコアのみを使用できます。',
            'password.required' =>'パスワードを入力してください',
            'all_departments_id.required' =>'所属を選択してください',
            'positions_id.required' =>'役職を選択してください',
        ];
    }
}
