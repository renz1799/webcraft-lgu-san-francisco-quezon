<?php

namespace App\Http\Requests\Theme;

use Illuminate\Foundation\Http\FormRequest;

class UpdateThemeStyleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        return [
            'mode'        => ['sometimes','in:light,dark'],
            'dir'         => ['sometimes','in:ltr,rtl'],
            'nav'         => ['sometimes','in:vertical,horizontal'],

            // canonical
            'menuStyle'   => ['sometimes','in:menu-click,menu-hover,icon-click,icon-hover'],

            // legacy / alt names
            'menu_style'  => ['sometimes','in:menu-click,menu-hover,icon-click,icon-hover'],
            'menuHover'   => ['sometimes','boolean'], // will be mapped
        ];
    }

    protected function prepareForValidation(): void
    {
        $data = $this->all();

        // normalize booleans and synonyms
        if (array_key_exists('menu_style', $data) && !isset($data['menuStyle'])) {
            $data['menuStyle'] = $data['menu_style'];
        }

        // legacy `menuHover` to a menuStyle (best guess)
        if (array_key_exists('menuHover', $data)) {
            $hover = filter_var($data['menuHover'], FILTER_VALIDATE_BOOL);
            // don’t override if explicit menuStyle present
            if (!isset($data['menuStyle'])) {
                $data['menuStyle'] = $hover ? 'menu-hover' : 'menu-click';
            }
            unset($data['menuHover']); // drop legacy key
        }

        $this->replace($data);
    }

    public function validated($key = null, $default = null)
    {
        $v = parent::validated($key, $default);

        // keep only canonical keys in service boundary
        $out = [];
        foreach (['mode','dir','nav','menuStyle'] as $k) {
            if (array_key_exists($k, $v)) $out[$k] = $v[$k];
        }
        return $out;
    }
}
