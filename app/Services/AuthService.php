<?php

namespace App\Services;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Contracts\Services\AuthServiceInterface;
use App\Contracts\Services\UserServiceInterface;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;

class AuthService implements AuthServiceInterface
{
    public function __construct(
        private readonly UserServiceInterface $userService,
        private readonly UserRepositoryInterface $userRepository
    ) {}

    public function register(array $data): array
    {
        $user = $this->userService->create($data);

        return [
            'user' => $user,
            'token' => Auth::guard('api')->login($user),
        ];
    }

    public function login(array $credentials): ?string
    {
        $token = Auth::guard('api')->attempt($credentials);

        return $token ?: null;
    }

    public function logout(): void
    {
        Auth::guard('api')->logout();
    }

    public function refresh(): string
    {
        return Auth::guard('api')->parseToken()->refresh();
    }

    public function forgotPassword(array $data): void
    {
        Password::sendResetLink($data);
    }

    public function verifyResetToken(string $email, string $token): ?bool
    {
        $user = $this->userRepository->findByEmail($email);

        if (! $user) {
            return null;
        }

        return Password::broker()->tokenExists($user, $token);
    }

    public function resetPassword(array $data): string
    {
        return Password::reset($data, function (User $user, string $password) {
            $user->password = $password;
            $user->save();
        });
    }
}
