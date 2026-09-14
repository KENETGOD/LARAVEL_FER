<?php

namespace Tests\Unit;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Support\Facades\Hash;
use Mockery;
use Tests\TestCase;

class UserServiceTest extends TestCase
{
    public function test_create_hashes_password_before_using_repository(): void
    {
        Hash::shouldReceive('make')->once()->with('secret')->andReturn('hashed-secret');
        $repository = Mockery::mock(UserRepositoryInterface::class);
        $user = new User(['name' => 'Ana', 'email' => 'ana@example.com']);
        $repository->shouldReceive('create')->once()->with([
            'name' => 'Ana',
            'email' => 'ana@example.com',
            'password' => 'hashed-secret',
        ])->andReturn($user);

        $this->assertSame($user, (new UserService($repository))->create([
            'name' => 'Ana',
            'email' => 'ana@example.com',
            'password' => 'secret',
        ]));
    }

    public function test_update_returns_null_without_loading_or_writing_a_missing_user(): void
    {
        $repository = Mockery::mock(UserRepositoryInterface::class);
        $repository->shouldReceive('find')->once()->with(9)->andReturnNull();

        $this->assertNull((new UserService($repository))->update(9, ['name' => 'Ana']));
    }

    public function test_delete_returns_false_without_deleting_a_missing_user(): void
    {
        $repository = Mockery::mock(UserRepositoryInterface::class);
        $repository->shouldReceive('find')->once()->with(9)->andReturnNull();

        $this->assertFalse((new UserService($repository))->delete(9));
    }
}
