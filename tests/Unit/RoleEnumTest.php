<?php

namespace Tests\Unit;

use App\Enums\Role;
use Tests\TestCase;

class RoleEnumTest extends TestCase
{
    public function test_role_values_match_database_strings(): void
    {
        $this->assertSame('user', Role::User->value);
        $this->assertSame('admin', Role::Admin->value);
    }

    public function test_role_labels_are_human_readable(): void
    {
        $this->assertSame('User', Role::User->label());
        $this->assertSame('Administrator', Role::Admin->label());
    }
}
