<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Seed the starter service catalog (8 categories, 38 services) from
 * db/data/catalog.json — the same content the old database.sql produced.
 *
 * Existing data is never replaced: if either table already has rows, that
 * table is left untouched. The matching down() is intentionally a no-op;
 * rollback of the schema migration drops the tables.
 */
final class SeedServiceCatalog extends AbstractMigration
{
    public function up(): void
    {
        $data = json_decode(
            (string) file_get_contents(__DIR__ . '/../data/catalog.json'),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        if ((int) $this->fetchRow('SELECT COUNT(*) AS c FROM service_categories')['c'] === 0) {
            $this->table('service_categories')->insert($data['categories'])->saveData();
        }

        if ((int) $this->fetchRow('SELECT COUNT(*) AS c FROM services')['c'] === 0) {
            $this->table('services')->insert($data['services'])->saveData();
        }
    }

    public function down(): void
    {
        // Data migrations are not reversed — see the class docblock.
    }
}
