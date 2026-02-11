<?php

declare(strict_types=1);

namespace Fleet\Interface\Http\CreateBike;

use Fleet\Domain\BrakeType;
use Fleet\Domain\FrameSizeLetter;
use Fleet\Domain\FrameSizeUnit;
use Fleet\Domain\WheelSize;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class CreateBikeRequest extends FormRequest
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
            // Champs obligatoires
            'internal_number' => ['required', 'string', 'max:50'],
            'model_id' => ['required', 'string', 'uuid'],
            'category_id' => ['required', 'string', 'uuid'],
            'frame_size_unit' => ['required', 'string', Rule::in(array_column(FrameSizeUnit::cases(), 'value'))],
            'frame_size_numeric' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'frame_size_letter' => ['nullable', 'string', Rule::in(array_column(FrameSizeLetter::cases(), 'value'))],

            // Champs optionnels
            'year' => ['nullable', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],
            'serial_number' => ['nullable', 'string', 'max:100'],
            'color' => ['nullable', 'string', 'max:50'],
            'wheel_size' => ['nullable', 'string', Rule::in(array_column(WheelSize::cases(), 'value'))],
            'front_suspension' => ['nullable', 'integer', 'min:0', 'max:300'],
            'rear_suspension' => ['nullable', 'integer', 'min:0', 'max:300'],
            'brake_type' => ['nullable', 'string', Rule::in(array_column(BrakeType::cases(), 'value'))],
            'purchase_price' => ['nullable', 'numeric', 'min:0'],
            'purchase_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'photos' => ['nullable', 'array'],
            'photos.*' => ['string'],
        ];
    }

    protected function prepareForValidation(): void
    {
        // S'assurer que photos est un tableau vide si non fourni
        if (!$this->has('photos')) {
            $this->merge(['photos' => []]);
        }
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'internal_number.required' => 'Le numéro interne est obligatoire.',
            'model_id.required' => 'Le modèle est obligatoire.',
            'model_id.uuid' => 'Le modèle doit être un UUID valide.',
            'category_id.required' => 'La catégorie est obligatoire.',
            'category_id.uuid' => 'La catégorie doit être un UUID valide.',
            'frame_size_unit.required' => 'L\'unité de taille du cadre est obligatoire.',
            'year.integer' => 'L\'année doit être un nombre entier.',
            'year.min' => 'L\'année doit être supérieure à 1900.',
            'purchase_date.date' => 'La date d\'achat doit être une date valide.',
        ];
    }
}
