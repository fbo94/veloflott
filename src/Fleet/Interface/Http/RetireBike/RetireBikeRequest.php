<?php

declare(strict_types=1);

namespace Fleet\Interface\Http\RetireBike;

use Fleet\Domain\RetirementReason;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class RetireBikeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', Rule::in(array_column(RetirementReason::cases(), 'value'))],
            'comment' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'reason.required' => 'Le motif de retrait est obligatoire.',
            'reason.in' => 'Le motif de retrait doit être l\'une des valeurs suivantes : vendu, volé, hors service définitif, autre.',
            'comment.max' => 'Le commentaire ne peut pas dépasser 1000 caractères.',
        ];
    }
}
