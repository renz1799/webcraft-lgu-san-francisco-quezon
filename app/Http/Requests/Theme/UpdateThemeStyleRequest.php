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
            'mode'           => ['sometimes','in:light,dark'],
            'dir'            => ['sometimes','in:ltr,rtl'],
            'nav'            => ['sometimes','in:vertical,horizontal'],

            // canonical
            'menuStyle'      => ['sometimes','in:menu-click,menu-hover,icon-click,icon-hover'],
            'sideMenuLayout' => ['sometimes','in:default,closed,icontext,icon-overlay,detached,doublemenu'],
            'pageStyle'      => ['sometimes','in:regular,classic,modern'],

            // legacy / alt names (mapped in prepareForValidation)
            'menu_style'               => ['sometimes','in:menu-click,menu-hover,icon-click,icon-hover'],
            'menuHover'                => ['sometimes','boolean'],

            'side_menu_layout'         => ['sometimes','in:default,closed,icontext,icon-overlay,detached,doublemenu'],
            'sidemenu_layout'          => ['sometimes','in:default,closed,icontext,icon-overlay,detached,doublemenu'],
            'sidemenu-layout'          => ['sometimes','in:default,closed,icontext,icon-overlay,detached,doublemenu'],
            'sidemenu-layout-styles'   => ['sometimes','in:default,closed,icontext,icon-overlay,detached,doublemenu'],
            'vertical_style'           => ['sometimes','in:default,closed,icontext,icon-overlay,detached,doublemenu'],
            'vertical-style'           => ['sometimes','in:default,closed,icontext,icon-overlay,detached,doublemenu'],

            'page_style'               => ['sometimes','in:regular,classic,modern'],
            'data_page_style'          => ['sometimes','in:regular,classic,modern'],
            'data-page-style'          => ['sometimes','in:regular,classic,modern'],
            'data_page_styles'         => ['sometimes','in:regular,classic,modern'],
            'data-page-styles'         => ['sometimes','in:regular,classic,modern'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $data = $this->all();

        // normalize casing/whitespace on string values
        foreach ([
            'mode','dir','nav',
            'menuStyle','menu_style',
            'sideMenuLayout','side_menu_layout','sidemenu_layout','sidemenu-layout','sidemenu-layout-styles','vertical_style','vertical-style',
            'pageStyle','page_style','data_page_style','data-page-style','data_page_styles','data-page-styles',
        ] as $k) {
            if (isset($data[$k]) && is_string($data[$k])) {
                $data[$k] = strtolower(trim($data[$k]));
            }
        }

        // ---- menuStyle mappings ----
        if (array_key_exists('menu_style', $data) && !isset($data['menuStyle'])) {
            $data['menuStyle'] = $data['menu_style'];
        }
        if (array_key_exists('menuHover', $data)) {
            $hover = filter_var($data['menuHover'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if (!isset($data['menuStyle']) && $hover !== null) {
                $data['menuStyle'] = $hover ? 'menu-hover' : 'menu-click';
            }
            unset($data['menuHover']);
        }

        // ---- sideMenuLayout mappings ----
        foreach (['side_menu_layout','sidemenu_layout','sidemenu-layout','sidemenu-layout-styles','vertical_style','vertical-style'] as $alias) {
            if (array_key_exists($alias, $data) && !isset($data['sideMenuLayout'])) {
                $data['sideMenuLayout'] = $data[$alias];
            }
            unset($data[$alias]);
        }

        // ---- pageStyle mappings ----
        foreach (['page_style','data_page_style','data-page-style','data_page_styles','data-page-styles'] as $alias) {
            if (array_key_exists($alias, $data) && !isset($data['pageStyle'])) {
                $data['pageStyle'] = $data[$alias];
            }
            unset($data[$alias]);
        }

        $this->replace($data);
    }

    public function validated($key = null, $default = null)
    {
        $v = parent::validated($key, $default);

        // only canonical keys are returned
        $out = [];
        foreach (['mode','dir','nav','menuStyle','sideMenuLayout','pageStyle'] as $k) {
            if (array_key_exists($k, $v)) $out[$k] = $v[$k];
        }
        return $out;
    }
}
