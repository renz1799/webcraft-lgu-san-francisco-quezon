<?php

namespace App\Core\Data\Users;

final class ModuleUserOnboardingData
{
    public function __construct(
        public readonly string $firstName,
        public readonly ?string $middleName,
        public readonly string $lastName,
        public readonly ?string $nameExtension,
        public readonly string $email,
        public readonly string $role,
        public readonly ?string $departmentId,
        public readonly bool $isActive,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            firstName: trim((string) ($data['first_name'] ?? '')),
            middleName: self::nullableString($data['middle_name'] ?? null),
            lastName: trim((string) ($data['last_name'] ?? '')),
            nameExtension: self::nullableString($data['name_extension'] ?? null),
            email: mb_strtolower(trim((string) ($data['email'] ?? ''))),
            role: trim((string) ($data['role'] ?? '')),
            departmentId: self::nullableString($data['department_id'] ?? null),
            isActive: (bool) ($data['is_active'] ?? true),
        );
    }

    private static function nullableString(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
