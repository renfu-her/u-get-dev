<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class CreateBasicRolesAndPermissions extends Migration
{
    public function up()
    {
        // 創建基本權限
        $permissions = [
            // 使用者管理
            'view users',
            'create users',
            'edit users',
            'delete users',
            // 角色管理
            'view roles',
            'create roles',
            'edit roles',
            'delete roles',
            // 權限管理
            'view permissions',
            'create permissions',
            'edit permissions',
            'delete permissions',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // 創建超級管理員角色
        $superAdmin = Role::create([
            'name' => 'super-admin',
            'is_system' => true,
        ]);
        $superAdmin->givePermissionTo(Permission::all());

        // 創建管理員角色
        $admin = Role::create([
            'name' => 'admin',
            'is_system' => true,
        ]);
        $admin->givePermissionTo([
            'view users',
            'create users',
            'edit users',
            'view roles',
            'view permissions',
        ]);

        // 創建一般使用者角色
        $user = Role::create([
            'name' => 'user',
            'is_system' => true,
        ]);
    }

    public function down()
    {
        // 刪除所有角色和權限
        Role::where('is_system', false)->delete();
        Permission::delete();
    }
}
