<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConvertImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'image' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,gif,bmp,webp,avif',
                'max:10240' // 10MB
            ],
            'format' => [
                'nullable',
                'string',
                'in:jpg,jpeg,png,webp,avif,gif'
            ],
            'quality' => [
                'nullable',
                'string',
                'in:ultra,high,medium,low'
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'image.required' => 'لطفا یک تصویر انتخاب کنید',
            'image.file' => 'فایل انتخاب شده معتبر نیست',
            'image.mimes' => 'فرمت تصویر باید یکی از موارد زیر باشد: jpg, jpeg, png, gif, bmp, webp, avif',
            'image.max' => 'حجم تصویر نباید بیشتر از 10 مگابایت باشد',
            'format.required' => 'لطفا فرمت خروجی را مشخص کنید',
            'format.in' => 'فرمت انتخاب شده معتبر نیست',
            'quality.integer' => 'کیفیت باید یک عدد صحیح باشد',
            'quality.min' => 'کیفیت باید حداقل 1 باشد',
            'quality.max' => 'کیفیت باید حداکثر 100 باشد'
        ];
    }
}
