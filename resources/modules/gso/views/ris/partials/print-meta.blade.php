<table class="form-table">
    <colgroup>
        <col style="width: 13%;">
        <col style="width: 9%;">
        <col style="width: 44%;">
        <col style="width: 10%;">
        <col style="width: 10%;">
        <col style="width: 14%;">
    </colgroup>

    <tr>
        <td colspan="3" style="padding: 0;">
            <table class="header-inner">
                <colgroup>
                    <col style="width: 22%;">
                    <col style="width: 78%;">
                </colgroup>
                <tr>
                    <td class="divider bold">LGU:</td>
                    <td class="bold upper">MUNICIPALITY OF SAN FRANCISCO, QUEZON</td>
                </tr>
            </table>
        </td>

        <td colspan="3" style="padding: 0;">
            <table class="header-inner">
                <colgroup>
                    <col style="width: 27%;">
                    <col style="width: 73%;">
                </colgroup>
                <tr>
                    <td class="bold">Fund:</td>
                    <td>{{ $print['fund'] ?? '' }}</td>
                </tr>
            </table>
        </td>
    </tr>

    <tr>
        <td colspan="3" style="padding: 0;">
            <table class="header-inner">
                <colgroup>
                    <col style="width: 22%;">
                    <col style="width: 78%;">
                </colgroup>
                <tr>
                    <td class="divider bold">Division:</td>
                    <td>{{ $ris->division ?? '' }}</td>
                </tr>
            </table>
        </td>

        <td colspan="3" style="padding: 0;">
            <table class="header-inner">
                <colgroup>
                    <col style="width: 27%;">
                    <col style="width: 73%;">
                </colgroup>
                <tr>
                    <td class="bold">FPP Code:</td>
                    <td>{{ $ris->fpp_code ?? '' }}</td>
                </tr>
            </table>
        </td>
    </tr>

    <tr>
        <td colspan="3" style="padding: 0;">
            <table class="header-inner">
                <colgroup>
                    <col style="width: 22%;">
                    <col style="width: 78%;">
                </colgroup>
                <tr>
                    <td class="divider bold">Office:</td>
                    <td>{{ $print['office'] ?? ($ris->requesting_department_name_snapshot ?? '') }}</td>
                </tr>
            </table>
        </td>

        <td colspan="3" style="padding: 0;">
            <table class="header-inner">
                <colgroup>
                    <col style="width: 27%;">
                    <col style="width: 73%;">
                </colgroup>
                <tr>
                    <td class="bold">RIS No.:</td>
                    <td>
                        {{ $print['ris_no'] ?? ($ris->ris_number ?? '') }}
                        @if(!empty($print['ris_date']))
                            <span class="small" style="margin-left: 8px;">{{ $print['ris_date'] }}</span>
                        @endif
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
