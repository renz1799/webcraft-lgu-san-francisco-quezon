<?php

namespace App\Modules\GSO\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class GsoWorkspaceController extends Controller
{
    private const PAGES = [
        'ris' => [
            'title' => 'RIS',
            'description' => 'Requisition and Issue Slip workflows are reserved in the platform and will be migrated into the GSO documents area next.',
        ],
        'pars' => [
            'title' => 'PAR',
            'description' => 'Property Acknowledgment Receipt workflows will be migrated here on the next document wave.',
        ],
        'ics' => [
            'title' => 'ICS',
            'description' => 'Inventory Custodian Slip document workflows are queued for the next GSO document migration slice.',
        ],
        'ptrs' => [
            'title' => 'PTR',
            'description' => 'Property Transfer Report workflows will move here once the AIR and downstream inventory layers are complete.',
        ],
        'itrs' => [
            'title' => 'ITR',
            'description' => 'Inventory Transfer Report workflows are reserved here for the next GSO document migration wave.',
        ],
        'items' => [
            'title' => 'Items',
            'description' => 'Legacy item master-data and catalog management are being migrated into the platform module.',
        ],
        'inventory-items' => [
            'title' => 'Inventory Items',
            'description' => 'Inventory item records, public asset pages, files, and reports will live here after wave 1.',
        ],
        'inspections' => [
            'title' => 'Inspections',
            'description' => 'Inspection workflows and follow-up actions are being integrated as the first GSO workflow slice.',
        ],
        'stocks' => [
            'title' => 'Stocks',
            'description' => 'Stock ledgers, movement history, and report workspaces will be hosted under this module area.',
        ],
        'reports' => [
            'title' => 'Reports',
            'description' => 'Cross-document and inventory reporting pages will be migrated here so the GSO module keeps one shared reports workspace.',
        ],
        'reports-rpci' => [
            'title' => 'RPCI',
            'description' => 'The Report on the Physical Count of Inventories menu entry is reserved here while the shared GSO reports workspace is being migrated.',
        ],
        'reports-rpcppe' => [
            'title' => 'RPCPPE',
            'description' => 'The Report on the Physical Count of Property, Plant, and Equipment will be surfaced from this reports area once the legacy entry is fully unified here.',
        ],
        'reports-rpcsp' => [
            'title' => 'RPCSP',
            'description' => 'The Report on the Physical Count of Semi-Expendable Property is reserved here while report navigation is moved into the module sidebar.',
        ],
        'reports-regspi' => [
            'title' => 'RegSPI',
            'description' => 'The Register of Semi-Expendable Property Issued menu entry will be reconnected from this shared reports area.',
        ],
        'reports-rspi' => [
            'title' => 'RSPI',
            'description' => 'The Report on Semi-Expendable Property Issued placeholder is reserved here for the next GSO reports migration wave.',
        ],
        'reports-rrsp' => [
            'title' => 'RRSP',
            'description' => 'The Report of Returned Semi-Expendable Property placeholder is queued here for the shared reports workspace.',
        ],
        'reports-ssmi' => [
            'title' => 'SSMI',
            'description' => 'The Summary of Supplies and Materials Issued placeholder is reserved here while stock and issuance reports are consolidated.',
        ],
        'reports-stock-card' => [
            'title' => 'Stock Card',
            'description' => 'The stock card reporting entry will be surfaced from this reports menu once the shared reports workspace is fully connected.',
        ],
        'asset-types' => [
            'title' => 'Asset Types',
            'description' => 'Reference data for asset types will be migrated before the document workflows that depend on them.',
        ],
        'asset-categories' => [
            'title' => 'Asset Categories',
            'description' => 'Asset category management is reserved as part of the reference-data migration wave.',
        ],
        'departments' => [
            'title' => 'Departments',
            'description' => 'GSO department-facing reference data will move here while keeping platform identity records in Core.',
        ],
        'fund-sources' => [
            'title' => 'Fund Sources',
            'description' => 'Fund source records are queued for the first GSO migration wave.',
        ],
        'fund-clusters' => [
            'title' => 'Fund Clusters',
            'description' => 'Fund cluster maintenance will be migrated together with other GSO reference tables.',
        ],
        'accountable-officers' => [
            'title' => 'Accountable Officers',
            'description' => 'Accountable officer lookups for PAR, ICS, PTR, and ITR workflows will move into this module section.',
        ],
    ];

    public function show(string $page): View
    {
        abort_unless(isset(self::PAGES[$page]), 404);

        return view('gso::shell.page', [
            'pageKey' => $page,
            'page' => self::PAGES[$page],
        ]);
    }
}
