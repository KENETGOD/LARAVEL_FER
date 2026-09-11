# Graph Report - Ense-ar-Laravel  (2026-08-18)

## Corpus Check
- Corpus is ~33,199 words - fits in a single context window. You may not need a graph.

## Summary
- 652 nodes · 1282 edges · 53 communities (37 shown, 16 thin omitted)
- Extraction: 97% EXTRACTED · 3% INFERRED · 0% AMBIGUOUS · INFERRED: 37 edges (avg confidence: 0.8)
- Token cost: 0 input · 0 output

## Community Hubs (Navigation)
- Community 0
- Community 1
- Community 2
- Community 3
- Community 4
- Community 5
- Community 6
- Community 7
- Community 8
- Community 9
- Community 10
- Community 11
- Community 12
- Community 13
- Community 14
- Community 15
- Community 16
- Community 17
- Community 18
- Community 19
- Community 20
- Community 21
- Community 22
- Community 23
- Community 25
- Community 26
- Community 27
- Community 28
- Community 29
- Community 30
- Community 31
- Community 32
- Community 33
- Community 34
- Community 35
- Community 36
- Community 37
- Community 38
- Community 39
- Community 40

## God Nodes (most connected - your core abstractions)
1. `User` - 62 edges
2. `Producto` - 38 edges
3. `Pedido` - 36 edges
4. `response()` - 34 edges
5. `Categoria` - 29 edges
6. `Etiqueta` - 27 edges
7. `CatalogoPublicoTest` - 20 edges
8. `PasswordRecoveryTest` - 17 edges
9. `PedidoApiTest` - 16 edges
10. `Controller` - 15 edges

## Surprising Connections (you probably didn't know these)
- `find()` --references--> `User`  [EXTRACTED]
  app/Contracts/Repositories/UserRepositoryInterface.php → app/Models/User.php
- `findByEmail()` --references--> `User`  [EXTRACTED]
  app/Contracts/Repositories/UserRepositoryInterface.php → app/Models/User.php
- `create()` --references--> `User`  [EXTRACTED]
  app/Contracts/Repositories/UserRepositoryInterface.php → app/Models/User.php
- `update()` --references--> `User`  [EXTRACTED]
  app/Contracts/Repositories/UserRepositoryInterface.php → app/Models/User.php
- `delete()` --references--> `User`  [EXTRACTED]
  app/Contracts/Repositories/UserRepositoryInterface.php → app/Models/User.php

## Import Cycles
- None detected.

## Communities (53 total, 16 thin omitted)

### Community 0 - "Community 0"
Cohesion: 0.08
Nodes (13): message(), statusCode(), CategoriaRequest, ForgotPasswordRequest, LoginRequest, RegisterRequest, ResetPasswordRequest, StorePedidoRequest (+5 more)

### Community 1 - "Community 1"
Cohesion: 0.04
Nodes (45): pestphp/pest-plugin, php-http/discovery, autoload, autoload-dev, psr-4, files, psr-4, config (+37 more)

### Community 2 - "Community 2"
Cohesion: 0.09
Nodes (14): create(), delete(), find(), paginate(), syncEtiquetas(), update(), withRelations(), create() (+6 more)

### Community 3 - "Community 3"
Cohesion: 0.11
Nodes (9): Illuminate\Foundation\Testing\RefreshDatabase, Illuminate\Foundation\Testing\TestCase, Spatie\Permission\DefaultTeamResolver, Spatie\Permission\Models\Permission, Spatie\Permission\Models\Role, ExampleTest, PedidoApiTest, UserApiErrorTest (+1 more)

### Community 4 - "Community 4"
Cohesion: 0.09
Nodes (3): Illuminate\Database\Migrations\Migration, Illuminate\Database\Schema\Blueprint, Illuminate\Support\Facades\Schema

### Community 5 - "Community 5"
Cohesion: 0.12
Nodes (15): attachProductos(), create(), find(), paginateAll(), paginateByUser(), updateEstado(), create(), getForUser() (+7 more)

### Community 6 - "Community 6"
Cohesion: 0.14
Nodes (11): CategoriaResource, EtiquetaResource, PedidoResource, ProductoResource, UserResource, ApiConstants, json_paginado(), Illuminate\Http\JsonResponse (+3 more)

### Community 7 - "Community 7"
Cohesion: 0.08
Nodes (26): scripts, dev, post-autoload-dump, post-create-project-cmd, post-root-package-install, post-update-cmd, pre-package-uninstall, setup (+18 more)

### Community 8 - "Community 8"
Cohesion: 0.13
Nodes (7): response(), AuthController, CategoriaController, DashboardController, PedidoController, Controller, Illuminate\Support\Facades\Route

### Community 9 - "Community 9"
Cohesion: 0.14
Nodes (8): User, UserRepository, Illuminate\Auth\Passwords\CanResetPassword, Illuminate\Database\Eloquent\Factories\HasFactory, Illuminate\Foundation\Auth\User, Illuminate\Notifications\Notifiable, PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject, Spatie\Permission\Traits\HasRoles

### Community 10 - "Community 10"
Cohesion: 0.10
Nodes (19): axios, concurrently, laravel-vite-plugin, devDependencies, axios, concurrently, laravel-vite-plugin, tailwindcss (+11 more)

### Community 12 - "Community 12"
Cohesion: 0.21
Nodes (7): create(), delete(), find(), paginate(), update(), Categoria, CategoriaRepository

### Community 13 - "Community 13"
Cohesion: 0.21
Nodes (7): create(), delete(), find(), paginate(), update(), Etiqueta, EtiquetaRepository

### Community 14 - "Community 14"
Cohesion: 0.12
Nodes (15): Illuminate\Auth\Access\AuthorizationException, Illuminate\Auth\AuthenticationException, Illuminate\Database\Eloquent\ModelNotFoundException, Illuminate\Foundation\Application, Illuminate\Foundation\Configuration\Exceptions, Illuminate\Foundation\Configuration\Middleware, PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException, Spatie\Permission\Exceptions\UnauthorizedException (+7 more)

### Community 15 - "Community 15"
Cohesion: 0.20
Nodes (5): DatabaseSeeder, DemoDataSeeder, RoleSeeder, Illuminate\Database\Console\Seeds\WithoutModelEvents, Illuminate\Database\Seeder

### Community 16 - "Community 16"
Cohesion: 0.14
Nodes (7): create(), delete(), find(), findByEmail(), paginate(), update(), UserService

### Community 18 - "Community 18"
Cohesion: 0.17
Nodes (8): findMany(), all(), create(), getAll(), getById(), paginate(), update(), Illuminate\Database\Eloquent\Collection

### Community 20 - "Community 20"
Cohesion: 0.22
Nodes (5): UserFactory, Illuminate\Database\Eloquent\Factories\Factory, Illuminate\Support\Str, Pdo\Mysql, static

### Community 21 - "Community 21"
Cohesion: 0.20
Nodes (5): create(), getById(), paginate(), update(), Illuminate\Validation\ValidationException

### Community 26 - "Community 26"
Cohesion: 0.36
Nodes (4): ResetPasswordNotification, Illuminate\Bus\Queueable, Illuminate\Notifications\Messages\MailMessage, Illuminate\Notifications\Notification

### Community 27 - "Community 27"
Cohesion: 0.29
Nodes (5): Illuminate\Support\Facades\Auth, Illuminate\Support\Facades\DB, Illuminate\Support\Facades\Notification, Illuminate\Support\Facades\Password, PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth

### Community 30 - "Community 30"
Cohesion: 0.33
Nodes (4): create(), getById(), paginate(), update()

### Community 35 - "Community 35"
Cohesion: 0.40
Nodes (4): Monolog\Handler\NullHandler, Monolog\Handler\StreamHandler, Monolog\Handler\SyslogUdpHandler, Monolog\Processor\PsrLogMessageProcessor

### Community 36 - "Community 36"
Cohesion: 0.50
Nodes (3): L5Swagger\CustomGeneratorInterface, L5Swagger\Generator, OpenApi\scan

## Knowledge Gaps
- **61 isolated node(s):** `OpenApiSchemas`, `$schema`, `name`, `type`, `description` (+56 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **16 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `Community 9` to `Community 32`, `Community 3`, `Community 5`, `Community 6`, `Community 11`, `Community 15`, `Community 16`, `Community 17`, `Community 18`, `Community 20`, `Community 24`, `Community 27`?**
  _High betweenness centrality (0.175) - this node is a cross-community bridge._
- **Why does `Producto` connect `Community 2` to `Community 32`, `Community 34`, `Community 3`, `Community 6`, `Community 11`, `Community 15`, `Community 21`?**
  _High betweenness centrality (0.053) - this node is a cross-community bridge._
- **Why does `Categoria` connect `Community 12` to `Community 32`, `Community 34`, `Community 3`, `Community 11`, `Community 15`, `Community 21`, `Community 28`?**
  _High betweenness centrality (0.037) - this node is a cross-community bridge._
- **What connects `OpenApiSchemas`, `$schema`, `name` to the rest of the system?**
  _61 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Community 0` be split into smaller, more focused modules?**
  _Cohesion score 0.07535460992907801 - nodes in this community are weakly interconnected._
- **Should `Community 1` be split into smaller, more focused modules?**
  _Cohesion score 0.043478260869565216 - nodes in this community are weakly interconnected._
- **Should `Community 2` be split into smaller, more focused modules?**
  _Cohesion score 0.0915915915915916 - nodes in this community are weakly interconnected._