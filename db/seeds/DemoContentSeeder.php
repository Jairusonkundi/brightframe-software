<?php
declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

/**
 * Optional sample content: 12 quote requests (with their requested
 * services), 10 contact messages, and 14 reviews in mixed moderation
 * states — the same demo set the old seed_data.sql provided.
 *
 * Run explicitly (it is not needed for a working site):
 *
 *     php vendor/bin/phinx seed:run -s DemoContentSeeder
 *     # or, from Composer:
 *     composer seed
 *
 * Every section is skipped when its table already contains rows, so this
 * is safe to run more than once. The homepage stat values are only filled
 * when they are still empty.
 */
final class DemoContentSeeder extends AbstractSeed
{
    public function run(): void
    {
        $data = json_decode(
            (string) file_get_contents(__DIR__ . '/../data/demo-content.json'),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $this->seedQuotes($data['quotes'], $data['quote_services']);
        $this->seedMessages($data['messages']);
        $this->seedReviews($data['reviews']);
        $this->seedStatValues($data['site_stats'] ?? []);
    }

    /**
     * @param array<int, array<string, mixed>> $quotes
     * @param array<int, array<string, mixed>> $links
     */
    private function seedQuotes(array $quotes, array $links): void
    {
        if ((int) $this->fetchRow('SELECT COUNT(*) AS c FROM quote_requests')['c'] > 0) {
            $this->getOutput()->writeln('  <comment>quote_requests already has rows — skipping</comment>');
            return;
        }

        $rows       = [];
        $idsByEmail = [];

        foreach ($quotes as $i => $quote) {
            $id = $i + 1;
            $idsByEmail[$quote['email']] = $id;
            $rows[] = [
                'id'              => $id,
                'name'            => $quote['name'],
                'email'           => $quote['email'],
                'phone'           => $quote['phone'],
                'project_details' => $quote['project_details'],
                'budget_range'    => $quote['budget_range'],
                'timeline'        => $quote['timeline'],
                'submitted_at'    => $this->daysAgo((int) ($quote['days_ago'] ?? 0)),
                'status'          => $quote['status'],
            ];
        }

        $this->insert('quote_requests', $rows);

        $serviceIds = [];
        foreach ($this->fetchAll('SELECT id, title FROM services') as $service) {
            $serviceIds[$service['title']] = (int) $service['id'];
        }

        $linkRows = [];
        foreach ($links as $link) {
            if (!isset($idsByEmail[$link['quote_email']])) {
                continue;
            }
            $linkRows[] = [
                'quote_request_id' => $idsByEmail[$link['quote_email']],
                'service_id'       => $serviceIds[$link['service_title']] ?? null,
                'service_title'    => $link['service_title'],
            ];
        }

        if ($linkRows !== []) {
            $this->insert('quote_request_services', $linkRows);
        }
    }

    /** @param array<int, array<string, mixed>> $messages */
    private function seedMessages(array $messages): void
    {
        if ((int) $this->fetchRow('SELECT COUNT(*) AS c FROM contact_messages')['c'] > 0) {
            $this->getOutput()->writeln('  <comment>contact_messages already has rows — skipping</comment>');
            return;
        }

        $rows = [];
        foreach ($messages as $message) {
            $rows[] = [
                'name'         => $message['name'],
                'email'        => $message['email'],
                'subject'      => $message['subject'],
                'message'      => $message['message'],
                'submitted_at' => $this->daysAgo((int) ($message['days_ago'] ?? 0)),
                'status'       => $message['status'],
            ];
        }

        if ($rows !== []) {
            $this->insert('contact_messages', $rows);
        }
    }

    /** @param array<int, array<string, mixed>> $reviews */
    private function seedReviews(array $reviews): void
    {
        if ((int) $this->fetchRow('SELECT COUNT(*) AS c FROM reviews')['c'] > 0) {
            $this->getOutput()->writeln('  <comment>reviews already has rows — skipping</comment>');
            return;
        }

        $rows = [];
        foreach ($reviews as $review) {
            $rows[] = [
                'name'          => $review['name'],
                'company'       => $review['company'],
                'rating'        => (int) $review['rating'],
                'review_text'   => $review['review_text'],
                'status'        => $review['status'],
                'reject_reason' => $review['reject_reason'],
                'submitted_at'  => $this->daysAgo((int) ($review['days_ago'] ?? 0)),
            ];
        }

        if ($rows !== []) {
            $this->insert('reviews', $rows);
        }
    }

    /** @param array<int, array<string, mixed>> $stats */
    private function seedStatValues(array $stats): void
    {
        $connection = $this->getAdapter()->getConnection();

        foreach ($stats as $stat) {
            $value = $connection->quote((string) $stat['value']);
            $label = $connection->quote((string) $stat['label']);
            $this->execute(
                "UPDATE site_stats SET value = {$value} "
                . "WHERE label = {$label} AND (value IS NULL OR value = '')"
            );
        }
    }

    private function daysAgo(int $days): string
    {
        return (new DateTimeImmutable('now'))
            ->modify('-' . max(0, $days) . ' days')
            ->format('Y-m-d H:i:s');
    }
}
