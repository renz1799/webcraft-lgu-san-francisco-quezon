<?php

namespace Database\Factories\Modules\GSO;

use App\Modules\GSO\Models\Air;
use App\Modules\GSO\Models\Department;
use App\Modules\GSO\Models\FundSource;
use App\Core\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AirFactory extends Factory
{
    protected $model = Air::class;

    public function definition(): array
    {
        $department = Department::query()->inRandomOrder()->first()
            ?? Department::factory()->create();

        $creator = User::query()->where('is_active', true)->inRandomOrder()->first()
            ?? User::factory()->create();

        $fundSource = FundSource::query()->inRandomOrder()->first();

        return [
            'po_number' => 'PO-' . $this->faker->numerify('####-##-####'),
            'po_date' => $this->faker->date(),
            'air_number' => 'AIR-' . now()->format('Y') . '-' . $this->faker->numerify('####'),
            'air_date' => $this->faker->date(),

            'invoice_number' => 'INV-' . $this->faker->numerify('#####'),
            'invoice_date' => $this->faker->date(),

            'supplier_name' => $this->faker->company(),

            'requesting_department_id' => $department->id,
            'requesting_department_name_snapshot' => $department->name,
            'requesting_department_code_snapshot' => $department->code,

            'fund_source_id' => $fundSource?->id,
            'fund' => $fundSource?->name,

            'status' => $this->faker->randomElement(['draft', 'submitted', 'received', 'inspected']),

            'date_received' => null,
            'received_completeness' => null,
            'received_notes' => null,
            'date_inspected' => null,
            'inspection_verified' => null,
            'inspection_notes' => null,
            'inspected_by_name' => null,
            'accepted_by_name' => null,

            'created_by_user_id' => $creator->id,
            'created_by_name_snapshot' => trim(
                (string) (
                    optional($creator->profile)->full_name
                    ?? $creator->username
                    ?? $creator->email
                )
            ) ?: $creator->email,

            'remarks' => 'Seeded AIR for testing',
        ];
    }
}