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
use Illuminate\Support\ServiceProvider;

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
        //
    }
}
