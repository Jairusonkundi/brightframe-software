<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Public testimonials. Nothing is visible on the site until an admin
 * approves it (status = 'approved'). reject_reason is admin-only.
 */
final class CreateReviewsTable extends AbstractMigration
{
    public function up(): void
    {
        if ($this->hasTable('reviews')) {
            return;
        }

        $this->table('reviews', [
            'id'        => 'id',
            'signed'    => false,
            'engine'    => 'InnoDB',
            'collation' => 'utf8mb4_unicode_ci',
        ])
            ->addColumn('name', 'string', ['limit' => 150, 'null' => false])
            ->addColumn('company', 'string', ['limit' => 150, 'null' => true])
            ->addColumn('rating', 'tinyinteger', ['signed' => false, 'null' => false])
            ->addColumn('review_text', 'text', ['null' => false])
            ->addColumn('status', 'enum', [
                'values'  => ['pending', 'approved', 'rejected'],
                'default' => 'pending',
                'null'    => false,
            ])
            ->addColumn('reject_reason', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('display_order', 'integer', ['default' => 0, 'null' => false])
            ->addColumn('submitted_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => false])
            ->addIndex(['status'], ['name' => 'idx_status'])
            ->create();

        // MySQL 8+/MariaDB enforce this; older MySQL parses and ignores it.
        $this->execute(
            'ALTER TABLE `reviews` ADD CONSTRAINT `chk_reviews_rating` '
            . 'CHECK (`rating` BETWEEN 1 AND 5)'
        );
    }

    public function down(): void
    {
        if ($this->hasTable('reviews')) {
            $this->table('reviews')->drop()->save();
        }
    }
}
