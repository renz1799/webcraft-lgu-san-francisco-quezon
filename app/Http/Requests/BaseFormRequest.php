<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

abstract class BaseFormRequest extends FormRequest
{
    /**
     * Common input normalization that runs BEFORE rules().
     */
    protected function prepareForValidation(): void
    {
        $data = $this->all();

        // Trim all strings
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $data[$key] = trim($value);
            }
        }

        // Normalize email
        if (isset($data['email'])) {
            $data['email'] = mb_strtolower($data['email']);
        }

        // Standardize booleans if present
        foreach (['remember', 'is_active'] as $boolKey) {
            if ($this->has($boolKey)) {
                $data[$boolKey] = $this->boolean($boolKey);
            }
        }

        $this->merge($data);
    }

    /**
     * Enable "stop on first failure" without redeclaring the property.
     */
    protected function getValidatorInstance(): Validator
    {
        $validator = parent::getValidatorInstance();

        if ($this->stopOnFirstFailureEnabled()) {
            $validator->stopOnFirstFailure();
        }

        return $validator;
    }

    /**
     * Toggle per-request if you ever need to.
     */
    protected function stopOnFirstFailureEnabled(): bool
    {
        return true; // set false to disable globally
    }

    /**
     * Default authorization; override in child requests when needed.
     */
    public function authorize(): bool
    {
        return $this->user() !== null; // default: must be logged in
    }

    /**
     * Helper to return sanitized, validated data (optionally limited to keys).
     */
    public function payload(?array $only = null): array
    {
        $validated = $this->validated();
        return $only ? array_intersect_key($validated, array_flip($only)) : $validated;
    }
}
