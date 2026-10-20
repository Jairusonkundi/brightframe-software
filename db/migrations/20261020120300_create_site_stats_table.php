<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Admin-editable homepage stat tiles. Values intentionally start empty —
 * includes/site-stats.php renders nothing until real numbers are entered
 * in admin/stats.php.
 */
final class CreateSiteStatsTable extends AbstractMigration
{
    public function up(): void
    {
        if ($this->hasTable('site_stats')) {
            return;
        }

        $this->table('site_stats', [
            'id'        => 'id',
            'signed'    => false,
            'engine'    => 'InnoDB',
            'collation' => 'utf8mb4_unicode_ci',
        ])
            ->addColumn('label', 'string', ['limit' => 100, 'null' => false])
            ->addColumn('value', 'string', ['limit' => 50, 'null' => true])
            ->addColumn('display_order', 'integer', ['default' => 0, 'null' => false])
            ->create();
    }

    public function down(): void
    {
        if ($this->hasTable('site_stats')) {
            $this->table('site_stats')->drop()->save();
        }
    }
}
