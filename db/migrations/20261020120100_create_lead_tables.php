<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Lead capture: quote requests (+ requested services) and contact messages.
 */
final class CreateLeadTables extends AbstractMigration
{
    public function up(): void
    {
        if (!$this->hasTable('quote_requests')) {
            $this->table('quote_requests', [
                'id'        => 'id',
                'signed'    => false,
                'engine'    => 'InnoDB',
                'collation' => 'utf8mb4_unicode_ci',
            ])
                ->addColumn('name', 'string', ['limit' => 150, 'null' => false])
                ->addColumn('email', 'string', ['limit' => 150, 'null' => false])
                ->addColumn('phone', 'string', ['limit' => 40, 'null' => false])
                ->addColumn('project_details', 'text', ['null' => false])
                ->addColumn('budget_range', 'string', ['limit' => 60, 'null' => true])
                ->addColumn('timeline', 'string', ['limit' => 60, 'null' => true])
                ->addColumn('submitted_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => false])
                ->addColumn('status', 'enum', [
                    'values'  => ['new', 'contacted', 'in_discussion', 'closed'],
                    'default' => 'new',
                    'null'    => false,
                ])
                ->addIndex(['status'], ['name' => 'idx_status'])
                ->addIndex(['submitted_at'], ['name' => 'idx_submitted_at'])
                ->create();
        }

        if (!$this->hasTable('quote_request_services')) {
            // service_id deliberately has no foreign key: deleting or
            // renaming a catalog service must never block or corrupt a
            // historical lead. service_title snapshots the name at
            // submission time.
            $this->table('quote_request_services', [
                'id'        => 'id',
                'signed'    => false,
                'engine'    => 'InnoDB',
                'collation' => 'utf8mb4_unicode_ci',
            ])
                ->addColumn('quote_request_id', 'integer', ['signed' => false, 'null' => false])
                ->addColumn('service_id', 'integer', ['signed' => false, 'null' => true])
                ->addColumn('service_title', 'string', ['limit' => 150, 'null' => false])
                ->addIndex(['quote_request_id'], ['name' => 'idx_quote_request'])
                ->addIndex(['service_id'], ['name' => 'idx_service_id'])
                ->addForeignKey('quote_request_id', 'quote_requests', 'id', [
                    'delete'     => 'CASCADE',
                    'constraint' => 'fk_qrs_quote',
                ])
                ->create();
        }

        if (!$this->hasTable('contact_messages')) {
            $this->table('contact_messages', [
                'id'        => 'id',
                'signed'    => false,
                'engine'    => 'InnoDB',
                'collation' => 'utf8mb4_unicode_ci',
            ])
                ->addColumn('name', 'string', ['limit' => 150, 'null' => false])
                ->addColumn('email', 'string', ['limit' => 150, 'null' => false])
                ->addColumn('subject', 'string', ['limit' => 150, 'null' => false])
                ->addColumn('message', 'text', ['null' => false])
                ->addColumn('submitted_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => false])
                ->addColumn('status', 'enum', [
                    'values'  => ['new', 'read', 'replied'],
                    'default' => 'new',
                    'null'    => false,
                ])
                ->addIndex(['status'], ['name' => 'idx_status'])
                ->addIndex(['submitted_at'], ['name' => 'idx_submitted_at'])
                ->create();
        }
    }

    public function down(): void
    {
        if ($this->hasTable('quote_request_services')) {
            $this->table('quote_request_services')->drop()->save();
        }
        if ($this->hasTable('quote_requests')) {
            $this->table('quote_requests')->drop()->save();
        }
        if ($this->hasTable('contact_messages')) {
            $this->table('contact_messages')->drop()->save();
        }
    }
}
