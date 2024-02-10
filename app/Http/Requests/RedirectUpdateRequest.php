<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class RedirectUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'url' => 'required_without_all:status|active_url|starts_with:https://',
            'status' => 'required_without_all:url|boolean'
        ];
    }

    public function messages()
    {
        return [
            'required_without_all' => 'At least one field is required',
            'url.starts_with' => 'The field url have to start with https://',
            'url.active_url' => 'The field url have to be a active url',
            'status.boolean' => 'The field status have to be a boolean',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors()
        ], 422));
    }
}
