<?php

namespace App\Core\Support;

class PermissionNaming
{
    private const ACTION_LABELS = [
        'view' => 'View',
        'view_all' => 'View All',
        'create' => 'Create',
        'update' => 'Update',
        'submit' => 'Submit',
        'approve' => 'Approve',
        'reject' => 'Reject',
        'finalize' => 'Finalize',
        'reopen' => 'Reopen',
        'revert' => 'Revert',
        'inspect' => 'Inspect',
        'manage_items' => 'Manage Items',
        'manage_files' => 'Manage Files',
        'manage_events' => 'Manage Events',
        'manage_photos' => 'Manage Photos',
        'promote_inventory' => 'Promote Inventory',
        'generate_from_air' => 'Generate from AIR',
        'adjust' => 'Adjust',
        'adjust_stock' => 'Adjust Stock',
        'claim' => 'Claim',
        'comment' => 'Comment',
        'update_status' => 'Update Status',
        'reassign' => 'Reassign',
        'archive' => 'Archive',
        'restore' => 'Restore',
        'print' => 'Print',
        'connect' => 'Connect',
        'disconnect' => 'Disconnect',
        'deactivate' => 'Deactivate',
        'reset_password' => 'Reset Password',
        'view_access' => 'View Access',
        'manage_access' => 'Manage Access',
    ];

    private const TOKEN_LABELS = [
        'air' => 'AIR',
        'ris' => 'RIS',
        'par' => 'PAR',
        'ics' => 'ICS',
        'ptr' => 'PTR',
        'itr' => 'ITR',
        'wmr' => 'WMR',
        'rpci' => 'RPCI',
        'rpcppe' => 'RPCPPE',
        'rpcsp' => 'RPCSP',
        'regspi' => 'RegSPI',
        'rspi' => 'RSPI',
        'rrsp' => 'RRSP',
        'ssmi' => 'SSMI',
        'gso' => 'GSO',
        'api' => 'API',
    ];

    public static function normalizeKey(string $name): string
    {
        return strtolower(trim((string) preg_replace('/\s+/', ' ', $name)));
    }

    public static function isNormalized(string $name): bool
    {
        return preg_match('/^[a-z][a-z0-9_]*(?:\.[a-z][a-z0-9_]*)+$/', self::normalizeKey($name)) === 1;
    }

    public static function descriptor(string $name, ?string $page = null): array
    {
        $trimmed = trim($name);
        $normalized = self::normalizeKey($trimmed);

        if (self::isNormalized($normalized)) {
            $segments = explode('.', $normalized);
            $actionKey = self::normalizeAction(array_pop($segments) ?? '');
            $featureSegments = $segments;
            $resourceSegments = $featureSegments;

            if (count($resourceSegments) > 1 && in_array($resourceSegments[0], ['reports', 'access'], true)) {
                array_shift($resourceSegments);
            }

            $resourceSegments = $resourceSegments !== [] ? $resourceSegments : $featureSegments;
            $featureKey = implode('.', $featureSegments);
            $resourceKey = implode('.', $resourceSegments);

            return [
                'normalized' => true,
                'feature_key' => $featureKey,
                'resource_key' => $resourceKey,
                'resource_label' => self::humanizeSegments($resourceSegments),
                'action_key' => $actionKey,
                'action_label' => self::actionLabel($actionKey),
                'page_label' => self::pageDisplayName($page),
            ];
        }

        if (preg_match('/^([a-z]+)\s+(.+)$/i', $trimmed, $matches) === 1) {
            $actionKey = self::normalizeAction($matches[1]);
            $resourceLabel = self::humanizePhrase($matches[2]);

            return [
                'normalized' => false,
                'feature_key' => self::normalizeKey($matches[2]),
                'resource_key' => self::normalizeKey($matches[2]),
                'resource_label' => $resourceLabel,
                'action_key' => $actionKey,
                'action_label' => self::actionLabel($actionKey),
                'page_label' => self::pageDisplayName($page),
            ];
        }

        return [
            'normalized' => false,
            'feature_key' => $normalized,
            'resource_key' => $normalized,
            'resource_label' => self::humanizePhrase($trimmed),
            'action_key' => $normalized,
            'action_label' => self::humanizeToken($normalized),
            'page_label' => self::pageDisplayName($page),
        ];
    }

    public static function displayName(string $name, ?string $page = null): string
    {
        $descriptor = self::descriptor($name, $page);

        return trim($descriptor['resource_label'] . ' / ' . $descriptor['action_label']);
    }

    public static function pageDisplayName(?string $page): string
    {
        $page = trim((string) $page);
        if ($page === '') {
            return 'Uncategorized';
        }

        if (preg_match('/[A-Z\/]/', $page) === 1) {
            return (string) str($page)->replaceMatches('/\s+/', ' ')->trim();
        }

        return (string) str($page)
            ->replace('_', ' ')
            ->replaceMatches('/\s+/', ' ')
            ->trim()
            ->title();
    }

    public static function actionLabel(string $actionKey): string
    {
        $actionKey = self::normalizeAction($actionKey);

        return self::ACTION_LABELS[$actionKey] ?? self::humanizeToken($actionKey);
    }

    public static function actionKeys(): array
    {
        return array_keys(self::ACTION_LABELS);
    }

    public static function normalizeAction(string $action): string
    {
        return match (self::normalizeKey($action)) {
            'modify', 'edit' => 'update',
            'delete', 'remove' => 'archive',
            'show' => 'view',
            'add' => 'create',
            default => self::normalizeKey($action),
        };
    }

    private static function humanizeSegments(array $segments): string
    {
        return collect($segments)
            ->filter(fn (?string $segment): bool => filled($segment))
            ->map(fn (string $segment): string => self::humanizeToken($segment))
            ->implode(' ');
    }

    private static function humanizePhrase(string $phrase): string
    {
        return collect(preg_split('/[\s\.]+/', trim($phrase)) ?: [])
            ->filter(fn (?string $segment): bool => filled($segment))
            ->map(fn (string $segment): string => self::humanizeToken($segment))
            ->implode(' ');
    }

    private static function humanizeToken(string $token): string
    {
        $token = self::normalizeKey(str_replace('.', ' ', $token));

        if ($token === '') {
            return '';
        }

        if (isset(self::TOKEN_LABELS[$token])) {
            return self::TOKEN_LABELS[$token];
        }

        return (string) str($token)
            ->replace('_', ' ')
            ->replaceMatches('/\s+/', ' ')
            ->trim()
            ->title();
    }
}
