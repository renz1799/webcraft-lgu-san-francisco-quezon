<?php

namespace App\Core\Data\Auth;

final class RegisterUserData
{
    public function __construct(
        public readonly string $username,
        public readonly string $email,
        public readonly string $password,
        public readonly string $role,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            username: trim((string) ($data['username'] ?? '')),
            email: mb_strtolower(trim((string) ($data['email'] ?? ''))),
            password: (string) ($data['password'] ?? ''),
            role: trim((string) ($data['role'] ?? '')),
        );
    }

    public function toArray(): array
    {
        return [
            'username' => $this->username,
            'email' => $this->email,
            'password' => $this->password,
            'role' => $this->role,
        ];
    }
}