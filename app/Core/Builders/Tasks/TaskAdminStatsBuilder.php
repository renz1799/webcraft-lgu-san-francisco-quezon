<?php

namespace App\Core\Builders\Tasks;

use App\Core\Builders\Tasks\Contracts\TaskAdminStatsBuilderInterface;

class TaskAdminStatsBuilder implements TaskAdminStatsBuilderInterface
{
    public function build(array $rawStats): array
    {
        $windows = array_values((array) ($rawStats['windows'] ?? []));

        if (count($windows) < 2) {
            return [
                'period_label' => 'Last 0 months',
                'cards' => [],
                'chart' => [
                    'categories' => [],
                    'series' => [],
                ],
            ];
        }

        $currentWindow = $windows[count($windows) - 1];
        $previousWindow = $windows[count($windows) - 2];

        $currentKey = (string) ($currentWindow['key'] ?? '');
        $previousKey = (string) ($previousWindow['key'] ?? '');

        $newCounts = (array) ($rawStats['new_counts'] ?? []);
        $completedCounts = (array) ($rawStats['completed_counts'] ?? []);
        $pendingSnapshots = (array) ($rawStats['pending_snapshots'] ?? []);
        $inProgressSnapshots = (array) ($rawStats['in_progress_snapshots'] ?? []);
        $currentPending = (int) ($rawStats['current_pending'] ?? 0);
        $currentInProgress = (int) ($rawStats['current_in_progress'] ?? 0);
        $months = (int) ($rawStats['months'] ?? count($windows));

        return [
            'period_label' => "Last {$months} months",
            'cards' => [
                'new' => $this->makeMetricCard(
                    current: (int) ($newCounts[$currentKey] ?? 0),
                    previous: (int) ($newCounts[$previousKey] ?? 0),
                    contextLabel: 'this month',
                    comparisonLabel: 'Prev Month'
                ),
                'completed' => $this->makeMetricCard(
                    current: (int) ($completedCounts[$currentKey] ?? 0),
                    previous: (int) ($completedCounts[$previousKey] ?? 0),
                    contextLabel: 'this month',
                    comparisonLabel: 'Prev Month'
                ),
                'pending' => $this->makeMetricCard(
                    current: $currentPending,
                    previous: (int) ($pendingSnapshots[$previousKey] ?? 0),
                    contextLabel: 'open right now',
                    comparisonLabel: 'End of Last Month'
                ),
                'in_progress' => $this->makeMetricCard(
                    current: $currentInProgress,
                    previous: (int) ($inProgressSnapshots[$previousKey] ?? 0),
                    contextLabel: 'open right now',
                    comparisonLabel: 'End of Last Month'
                ),
            ],
            'chart' => [
                'categories' => array_values(array_map(
                    static fn (array $window) => (string) ($window['label'] ?? ''),
                    $windows
                )),
                'series' => [
                    [
                        'name' => 'New',
                        'data' => array_values($newCounts),
                    ],
                    [
                        'name' => 'Pending',
                        'data' => array_values($pendingSnapshots),
                    ],
                    [
                        'name' => 'Completed',
                        'data' => array_values($completedCounts),
                    ],
                    [
                        'name' => 'Inprogress',
                        'data' => array_values($inProgressSnapshots),
                    ],
                ],
            ],
        ];
    }

    private function makeMetricCard(
        int $current,
        int $previous,
        string $contextLabel,
        string $comparisonLabel
    ): array {
        $delta = $current - $previous;
        $direction = $delta === 0
            ? 'flat'
            : ($delta > 0 ? 'up' : 'down');

        $deltaPercent = $previous === 0
            ? ($current === 0 ? 0.0 : 100.0)
            : round((abs($delta) / $previous) * 100, 2);

        return [
            'value' => $current,
            'comparison_value' => $previous,
            'comparison_label' => $comparisonLabel,
            'context_label' => $contextLabel,
            'delta_percent' => $deltaPercent,
            'direction' => $direction,
        ];
    }
}
