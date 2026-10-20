<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Seed the homepage stat rows (labels only, values empty) and the initial
 * admin login.
 *
 * The password hash below matches the account that shipped with this
 * project. Change it after your first login, or generate a new one with:
 *
 *     php -r "echo password_hash('your-new-password', PASSWORD_DEFAULT), PHP_EOL;"
 *     UPDATE admin_users SET password_hash = '<new hash>' WHERE username = 'jairus';
 */
final class SeedSiteStatsAndAdminUser extends AbstractMigration
{
    private const SEED_ADMIN_USERNAME = 'jairus';
    private const SEED_ADMIN_HASH     = '$2y$10$P066ABqUZH22szs3kT2qSew5NcgPX83muAyucgG2mJbTP50gfXTS.';

    public function up(): void
    {
        if ((int) $this->fetchRow('SELECT COUNT(*) AS c FROM site_stats')['c'] === 0) {
            $this->table('site_stats')->insert([
                ['label' => 'Projects completed', 'value' => null, 'display_order' => 1],
                ['label' => 'Clients served', 'value' => null, 'display_order' => 2],
                ['label' => 'Businesses using our systems', 'value' => null, 'display_order' => 3],
            ])->saveData();
        }

        $existing = $this->fetchRow(
            "SELECT id FROM admin_users WHERE username = '" . self::SEED_ADMIN_USERNAME . "' LIMIT 1"
        );
        if (!$existing) {
            $this->table('admin_users')->insert([
                'username'      => self::SEED_ADMIN_USERNAME,
                'password_hash' => self::SEED_ADMIN_HASH,
            ])->saveData();
        }
    }

    public function down(): void
    {
        // Data migrations are not reversed — removing these rows would
        // delete real admin accounts/stats on rollback.
    }
}
