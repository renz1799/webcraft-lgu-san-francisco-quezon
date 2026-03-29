<?php

namespace Tests\Feature;

use App\Modules\GSO\Jobs\GenerateStickerPdfJob;
use App\Modules\GSO\Models\AssetCategory;
use App\Modules\GSO\Models\AssetType;
use App\Modules\GSO\Models\InventoryItem;
use App\Modules\GSO\Models\Item;
use App\Modules\GSO\Models\StickerPrintJob;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class StickerPrintJobTest extends TestCase
{
    use DatabaseTransactions;

    public function test_sticker_pdf_job_can_be_started_from_the_report_workspace(): void
    {
        $this->withoutMiddleware();
        Queue::fake();

        $inventoryItem = $this->createInventoryItem();

        $response = $this->postJson(route('gso.reports.stickers.jobs.store'), [
            'inventory_item_ids' => [$inventoryItem->id],
            'copies' => 3,
            'show_cut_guides' => true,
        ]);

        $response->assertStatus(202);
        $response->assertJsonPath('status', StickerPrintJob::STATUS_QUEUED);
        $response->assertJsonPath('progress_percent', 0);
        $response->assertJsonPath('completed_pages', 0);
        $response->assertJsonPath('total_pages', 0);
        $response->assertJsonPath(
            'status_url',
            route('gso.reports.stickers.jobs.show', ['stickerPrintJob' => $response->json('job_id')]),
        );
        $response->assertJsonPath(
            'download_url',
            route('gso.reports.stickers.jobs.download', ['stickerPrintJob' => $response->json('job_id')]),
        );

        $this->assertDatabaseHas('sticker_print_jobs', [
            'id' => $response->json('job_id'),
            'status' => StickerPrintJob::STATUS_QUEUED,
            'progress_percent' => 0,
        ]);

        $job = StickerPrintJob::query()->findOrFail($response->json('job_id'));

        $this->assertSame([$inventoryItem->id], $job->filters['inventory_item_ids'] ?? []);
        $this->assertSame(3, $job->filters['copies'] ?? null);
        $this->assertTrue((bool) ($job->filters['show_cut_guides'] ?? false));

        Queue::assertPushed(GenerateStickerPdfJob::class);
    }

    private function createInventoryItem(): InventoryItem
    {
        $assetType = AssetType::query()->create([
            'type_code' => 'TST-' . substr((string) fake()->uuid(), 0, 8),
            'type_name' => 'Sticker Test Type',
        ]);

        $assetCategory = AssetCategory::query()->create([
            'asset_type_id' => $assetType->id,
            'asset_code' => 'CAT-' . substr((string) fake()->uuid(), 0, 8),
            'asset_name' => 'Sticker Test Category',
            'account_group' => 'ICT Equipment',
            'is_selected' => false,
        ]);

        $item = Item::query()->create([
            'asset_id' => $assetCategory->id,
            'item_name' => 'Sticker Test Laptop',
            'description' => 'Sticker test laptop description',
            'base_unit' => 'unit',
            'item_identification' => 'STK-' . fake()->numerify('###'),
            'major_sub_account_group' => 'ICT Equipment',
            'tracking_type' => 'property',
            'requires_serial' => true,
            'is_semi_expendable' => false,
            'is_selected' => false,
        ]);

        return InventoryItem::query()->create([
            'item_id' => $item->id,
            'property_number' => 'PROP-' . fake()->numerify('#####'),
            'acquisition_date' => now()->toDateString(),
            'acquisition_cost' => 1200,
            'description' => 'Sticker test laptop description',
            'quantity' => 1,
            'unit' => 'unit',
            'stock_number' => 'STOCK-' . fake()->numerify('#####'),
            'is_ics' => false,
            'accountable_officer' => 'Sticker Test Officer',
            'custody_state' => 'pool',
            'status' => 'serviceable',
            'condition' => 'Brand New',
        ]);
    }
}
