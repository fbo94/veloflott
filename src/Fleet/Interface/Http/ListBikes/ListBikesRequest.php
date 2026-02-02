<?php

declare(strict_types=1);

namespace Fleet\Interface\Http\ListBikes;

use Fleet\Domain\BikeStatus;
use Fleet\Domain\FrameSizeLetter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class ListBikesRequest extends FormRequest
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
            'statuses' => ['nullable', 'array'],
            'statuses.*' => ['string', Rule::in(array_column(BikeStatus::cases(), 'value'))],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['string', 'uuid'],
            'frame_sizes' => ['nullable', 'array'],
            'frame_sizes.*' => ['string', Rule::in(array_column(FrameSizeLetter::cases(), 'value'))],
            'include_retired' => ['nullable', 'boolean'],
            'search' => ['nullable', 'string', 'max:255'],
            'sort_by' => ['nullable', 'string', Rule::in(['internal_number', 'brand', 'model', 'status', 'category_id', 'created_at', 'updated_at'])],
            'sort_direction' => ['nullable', 'string', Rule::in(['asc', 'desc'])],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    protected function prepareForValidation(): void
    {
        // Convertir les valeurs par défaut
        $this->merge([
            'include_retired' => $this->boolean('include_retired', false),
            'sort_by' => $this->input('sort_by', 'internal_number'),
            'sort_direction' => $this->input('sort_direction', 'asc'),
            'page' => $this->integer('page', 1),
            'per_page' => $this->integer('per_page', 50),
        ]);
    }
}
