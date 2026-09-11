<?php

namespace App\Contracts\Services;

use App\Models\User;

interface AuthServiceInterface
{
    /**
     * @param  array<string, mixed>  $data
     * @return array{user: User, token: string}
     */
    public function register(array $data): array;

    /**
     * @param  array<string, mixed>  $credentials
     */
    public function login(array $credentials): ?string;

    public function logout(): void;

    public function refresh(): string;

    /**
     * @param  array<string, mixed>  $data
     */
    public function forgotPassword(array $data): void;

    public function verifyResetToken(string $email, string $token): ?bool;

    /**
     * @param  array<string, mixed>  $data
     */
    public function resetPassword(array $data): string;
}
