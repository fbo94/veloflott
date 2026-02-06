<?php

declare(strict_types=1);

namespace Fleet\Interface\Http\UpdateSizeMappingConfiguration;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateSizeMappingConfigurationRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'xs_cm' => ['required', 'array:min,max'],
            'xs_cm.min' => ['required', 'integer', 'min:0', 'lte:xs_cm.max'],
            'xs_cm.max' => ['required', 'integer', 'min:0'],

            'xs_inch' => ['required', 'array:min,max'],
            'xs_inch.min' => ['required', 'integer', 'min:0', 'lte:xs_inch.max'],
            'xs_inch.max' => ['required', 'integer', 'min:0'],

            's_cm' => ['required', 'array:min,max'],
            's_cm.min' => ['required', 'integer', 'min:0', 'lte:s_cm.max'],
            's_cm.max' => ['required', 'integer', 'min:0'],

            's_inch' => ['required', 'array:min,max'],
            's_inch.min' => ['required', 'integer', 'min:0', 'lte:s_inch.max'],
            's_inch.max' => ['required', 'integer', 'min:0'],

            'm_cm' => ['required', 'array:min,max'],
            'm_cm.min' => ['required', 'integer', 'min:0', 'lte:m_cm.max'],
            'm_cm.max' => ['required', 'integer', 'min:0'],

            'm_inch' => ['required', 'array:min,max'],
            'm_inch.min' => ['required', 'integer', 'min:0', 'lte:m_inch.max'],
            'm_inch.max' => ['required', 'integer', 'min:0'],

            'l_cm' => ['required', 'array:min,max'],
            'l_cm.min' => ['required', 'integer', 'min:0', 'lte:l_cm.max'],
            'l_cm.max' => ['required', 'integer', 'min:0'],

            'l_inch' => ['required', 'array:min,max'],
            'l_inch.min' => ['required', 'integer', 'min:0', 'lte:l_inch.max'],
            'l_inch.max' => ['required', 'integer', 'min:0'],

            'xl_cm' => ['required', 'array:min,max'],
            'xl_cm.min' => ['required', 'integer', 'min:0', 'lte:xl_cm.max'],
            'xl_cm.max' => ['required', 'integer', 'min:0'],

            'xl_inch' => ['required', 'array:min,max'],
            'xl_inch.min' => ['required', 'integer', 'min:0', 'lte:xl_inch.max'],
            'xl_inch.max' => ['required', 'integer', 'min:0'],

            'xxl_cm' => ['required', 'array:min,max'],
            'xxl_cm.min' => ['required', 'integer', 'min:0', 'lte:xxl_cm.max'],
            'xxl_cm.max' => ['required', 'integer', 'min:0'],

            'xxl_inch' => ['required', 'array:min,max'],
            'xxl_inch.min' => ['required', 'integer', 'min:0', 'lte:xxl_inch.max'],
            'xxl_inch.max' => ['required', 'integer', 'min:0'],
        ];
    }
}
