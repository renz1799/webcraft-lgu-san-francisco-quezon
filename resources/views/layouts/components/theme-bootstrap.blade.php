@php
    $bootstrapThemeColors = $themeColors ?? [];
@endphp
<script>
    (function () {
        const colors = @json($bootstrapThemeColors);
        const html = document.documentElement;
        const colorKeys = ['primaryRGB', 'primaryRGB1', 'bodyBgRGB', 'darkBgRGB', 'ynexMenu', 'ynexHeader', 'bgimg'];

        const setValue = (key, value) => {
            if (typeof value === 'string' && value.trim() !== '') {
                localStorage.setItem(key, value.trim());
                return;
            }

            localStorage.removeItem(key);
        };

        colorKeys.forEach((key) => localStorage.removeItem(key));

        setValue('primaryRGB', colors.primaryRgb);
        setValue('primaryRGB1', colors.primaryRgb1);
        setValue('bodyBgRGB', colors.bodyBgRgb);
        setValue('darkBgRGB', colors.darkBgRgb);
        setValue('ynexMenu', colors.menu);
        setValue('ynexHeader', colors.header);
        setValue('bgimg', colors.bgImage);

        if (colors.menu) {
            html.setAttribute('data-menu-styles', colors.menu);
        }

        if (colors.header) {
            html.setAttribute('data-header-styles', colors.header);
        }

        if (colors.bgImage) {
            html.setAttribute('bg-img', colors.bgImage);
        } else {
            html.removeAttribute('bg-img');
        }

        if (colors.primaryRgb1) {
            html.style.setProperty('--primary', colors.primaryRgb1);
        }

        if (colors.primaryRgb) {
            html.style.setProperty('--primary-rgb', colors.primaryRgb);
        }

        if (colors.bodyBgRgb) {
            html.style.setProperty('--body-bg', colors.bodyBgRgb);
        }

        if (colors.darkBgRgb) {
            html.style.setProperty('--dark-bg', colors.darkBgRgb);
            html.style.setProperty('--light', colors.darkBgRgb);
        }
    })();
</script>
