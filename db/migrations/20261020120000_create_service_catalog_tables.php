<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Service catalog: categories + services.
 *
 * Safe to run against an existing installation — each table is only
 * created when it doesn't already exist.
 */
final class CreateServiceCatalogTables extends AbstractMigration
{
    public function up(): void
    {
        if (!$this->hasTable('service_categories')) {
            $this->table('service_categories', [
                'id'        => 'id',
                'signed'    => false,
                'engine'    => 'InnoDB',
                'collation' => 'utf8mb4_unicode_ci',
            ])
                ->addColumn('name', 'string', ['limit' => 150, 'null' => false])
                ->addColumn('slug', 'string', ['limit' => 150, 'null' => false])
                ->addColumn('display_order', 'integer', ['default' => 0, 'null' => false])
                ->addIndex(['slug'], ['unique' => true])
                ->create();
        }

        if (!$this->hasTable('services')) {
            $this->table('services', [
                'id'        => 'id',
                'signed'    => false,
                'engine'    => 'InnoDB',
                'collation' => 'utf8mb4_unicode_ci',
            ])
                ->addColumn('category_id', 'integer', ['signed' => false, 'null' => false])
                ->addColumn('title', 'string', ['limit' => 150, 'null' => false])
                ->addColumn('description', 'text', ['null' => false])
                ->addColumn('long_description', 'text', ['null' => true])
                ->addColumn('icon_name', 'string', ['limit' => 80, 'null' => false])
                ->addColumn('display_order', 'integer', ['default' => 0, 'null' => false])
                ->addIndex(['category_id'], ['name' => 'idx_category_id'])
                ->addForeignKey('category_id', 'service_categories', 'id', [
                    'delete'     => 'CASCADE',
                    'constraint' => 'fk_services_category',
                ])
                ->create();
        }
    }

    public function down(): void
    {
        if ($this->hasTable('services')) {
            $this->table('services')->drop()->save();
        }
        if ($this->hasTable('service_categories')) {
            $this->table('service_categories')->drop()->save();
        }
    }
}
