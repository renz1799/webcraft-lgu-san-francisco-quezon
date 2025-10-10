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
            'width'          => ['sometimes','in:fullwidth,boxed'],
            'menuPosition'   => ['sometimes','in:fixed,scrollable'],
            'headerPosition' => ['sometimes','in:fixed,scrollable'],
            'loader'         => ['sometimes','in:enable,disable'],

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

            'layout-width'            => ['sometimes','in:fullwidth,boxed'],
            'data-menu-positions'     => ['sometimes','in:fixed,scrollable'],
            'data-header-positions'   => ['sometimes','in:fixed,scrollable'],
            'page-loader'             => ['sometimes','in:enable,disable'],
        ];
    }

 protected function prepareForValidation(): void
{
    $data = $this->all();

    // normalize string casing
    foreach (array_keys($data) as $k) {
        if (is_string($data[$k])) $data[$k] = strtolower(trim($data[$k]));
    }

    // menuStyle aliases
    if (isset($data['menu_style']) && !isset($data['menuStyle'])) $data['menuStyle'] = $data['menu_style'];
    if (array_key_exists('menuHover', $data) && !isset($data['menuStyle'])) {
        $hover = filter_var($data['menuHover'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if ($hover !== null) $data['menuStyle'] = $hover ? 'menu-hover' : 'menu-click';
        unset($data['menuHover']);
    }

    // sideMenuLayout aliases
    foreach (['side_menu_layout','sidemenu_layout','sidemenu-layout','sidemenu-layout-styles','vertical_style','vertical-style'] as $a) {
        if (isset($data[$a]) && !isset($data['sideMenuLayout'])) $data['sideMenuLayout'] = $data[$a];
        unset($data[$a]);
    }

    // pageStyle aliases
    foreach (['data-page-styles','page_style'] as $a) {
        if (isset($data[$a]) && !isset($data['pageStyle'])) $data['pageStyle'] = $data[$a];
        unset($data[$a]);
    }

    // width / positions / loader aliases
    if (isset($data['layout-width']) && !isset($data['width'])) $data['width'] = $data['layout-width'];
    unset($data['layout-width']);

    if (isset($data['data-menu-positions']) && !isset($data['menuPosition'])) $data['menuPosition'] = $data['data-menu-positions'];
    unset($data['data-menu-positions']);

    if (isset($data['data-header-positions']) && !isset($data['headerPosition'])) $data['headerPosition'] = $data['data-header-positions'];
    unset($data['data-header-positions']);

    if (isset($data['page-loader']) && !isset($data['loader'])) $data['loader'] = $data['page-loader'];
    unset($data['page-loader']);

    $this->replace($data);
}

public function validated($key = null, $default = null)
{
    $v = parent::validated($key, $default);
    // return only canonical keys
    return array_intersect_key($v, array_flip([
        'mode','dir','nav','menuStyle','sideMenuLayout','pageStyle',
        'width','menuPosition','headerPosition','loader',
    ]));
}
}
