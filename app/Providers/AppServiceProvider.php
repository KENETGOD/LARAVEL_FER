<?php

namespace App\Providers;

use App\Contracts\Repositories\CategoriaRepositoryInterface;
use App\Contracts\Repositories\EtiquetaRepositoryInterface;
use App\Contracts\Repositories\PedidoRepositoryInterface;
use App\Contracts\Repositories\ProductoRepositoryInterface;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Contracts\Services\AuthServiceInterface;
use App\Contracts\Services\CategoriaServiceInterface;
use App\Contracts\Services\EtiquetaServiceInterface;
use App\Contracts\Services\PedidoServiceInterface;
use App\Contracts\Services\ProductoServiceInterface;
use App\Contracts\Services\UserServiceInterface;
use App\Repositories\CategoriaRepository;
use App\Repositories\EtiquetaRepository;
use App\Repositories\PedidoRepository;
use App\Repositories\ProductoRepository;
use App\Repositories\UserRepository;
use App\Services\AuthService;
use App\Services\CategoriaService;
use App\Services\EtiquetaService;
use App\Services\PedidoService;
use App\Services\ProductoService;
use App\Services\UserService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(UserServiceInterface::class, UserService::class);

        $this->app->bind(AuthServiceInterface::class, AuthService::class);

        $this->app->bind(CategoriaRepositoryInterface::class, CategoriaRepository::class);
        $this->app->bind(CategoriaServiceInterface::class, CategoriaService::class);

        $this->app->bind(EtiquetaRepositoryInterface::class, EtiquetaRepository::class);
        $this->app->bind(EtiquetaServiceInterface::class, EtiquetaService::class);

        $this->app->bind(ProductoRepositoryInterface::class, ProductoRepository::class);
        $this->app->bind(ProductoServiceInterface::class, ProductoService::class);

        $this->app->bind(PedidoRepositoryInterface::class, PedidoRepository::class);
        $this->app->bind(PedidoServiceInterface::class, PedidoService::class);
    }

    public function boot(): void
    {
        RateLimiter::for('auth-login', function (Request $request): array {
            return [
                Limit::perMinute(30)->by($request->ip()),
                Limit::perMinute(5)->by($this->emailKey($request)),
            ];
        });

        RateLimiter::for('auth-register', function (Request $request): Limit {
            return Limit::perMinute(10)->by($request->ip());
        });

        RateLimiter::for('auth-forgot-password', function (Request $request): array {
            return [
                Limit::perMinute(20)->by($request->ip()),
                Limit::perMinute(5)->by($this->emailKey($request)),
            ];
        });

        RateLimiter::for('auth-verify-reset-token', function (Request $request): array {
            return [
                Limit::perMinute(30)->by($request->ip()),
                Limit::perMinute(10)->by($this->emailKey($request)),
            ];
        });

        RateLimiter::for('auth-reset-password', function (Request $request): array {
            return [
                Limit::perMinute(20)->by($request->ip()),
                Limit::perMinute(5)->by($this->emailKey($request)),
            ];
        });

        RateLimiter::for('auth-refresh', function (Request $request): Limit {
            return Limit::perMinute(30)->by($request->ip());
        });
    }

    private function emailKey(Request $request): string
    {
        $email = Str::lower(Str::trim((string) $request->input('email')));

        return $request->ip().'|'.$email;
    }
}
