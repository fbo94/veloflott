<?php

declare(strict_types=1);

namespace Pricing\Interface\Http\ImportDefaultPricing;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @property-read string|null $source_tenant_id
 * @property-read string|null $target_tenant_id
 * @property-read bool $copy_pricing_classes
 * @property-read bool $copy_durations
 * @property-read bool $copy_rates
 * @property-read bool $copy_discount_rules
 */
final class ImportDefaultPricingRequest extends FormRequest
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
            'source_tenant_id' => ['nullable', 'uuid'],
            'target_tenant_id' => ['nullable', 'uuid'],
            'copy_pricing_classes' => ['boolean'],
            'copy_durations' => ['boolean'],
            'copy_rates' => ['boolean'],
            'copy_discount_rules' => ['boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'copy_pricing_classes' => $this->input('copy_pricing_classes', true),
            'copy_durations' => $this->input('copy_durations', true),
            'copy_rates' => $this->input('copy_rates', true),
            'copy_discount_rules' => $this->input('copy_discount_rules', true),
        ]);
    }
}
