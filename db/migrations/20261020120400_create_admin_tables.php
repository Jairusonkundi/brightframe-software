<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Admin authentication: users + IP-based login rate limiting.
 */
final class CreateAdminTables extends AbstractMigration
{
    public function up(): void
    {
        if (!$this->hasTable('admin_users')) {
            $this->table('admin_users', [
                'id'        => 'id',
                'signed'    => false,
                'engine'    => 'InnoDB',
                'collation' => 'utf8mb4_unicode_ci',
            ])
                ->addColumn('username', 'string', ['limit' => 60, 'null' => false])
                ->addColumn('password_hash', 'string', ['limit' => 255, 'null' => false])
                ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => false])
                ->addIndex(['username'], ['unique' => true])
                ->create();
        }

        if (!$this->hasTable('login_attempts')) {
            $this->table('login_attempts', [
                'id'        => 'id',
                'signed'    => false,
                'engine'    => 'InnoDB',
                'collation' => 'utf8mb4_unicode_ci',
            ])
                ->addColumn('ip_address', 'string', ['limit' => 45, 'null' => false])
                ->addColumn('attempted_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => false])
                ->addIndex(['ip_address', 'attempted_at'], ['name' => 'idx_ip_time'])
                ->create();
        }
    }

    public function down(): void
    {
        if ($this->hasTable('login_attempts')) {
            $this->table('login_attempts')->drop()->save();
        }
        if ($this->hasTable('admin_users')) {
            $this->table('admin_users')->drop()->save();
        }
    }
}
