<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class RedirectStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'url' => 'required|active_url|starts_with:https://'
        ];
    }

    public function messages()
    {
        return [
            'url.required' => 'The field url is required',
            'url.starts_with' => 'The field url have to start with https://',
            'url.active_url' => 'The field url have to be a active url',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors()
        ], 422));
    }
}
