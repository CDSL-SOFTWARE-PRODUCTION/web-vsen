<?php

namespace App\Console\Commands;

use App\Models\Demand\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ImportOrderItemsFromFixtureCommand extends Command
{
    protected $signature = 'ops:order-items-import
        {order : Order ID or order_code}
        {file=database/fixtures/order_items/pp26001148xx-delivery-schedule.json : Path to JSON fixture}
        {--status=planned : Initial order_item status}
        {--procurement_status=pending : Initial procurement_status}
        {--dry-run : Validate and preview without writing}';

    protected $description = 'Import order items from JSON fixture to a target order.';

    public function handle(): int
    {
        $orderArg = (string) $this->argument('order');
        $fileArg = (string) $this->argument('file');
        $status = (string) $this->option('status');
        $procurementStatus = (string) $this->option('procurement_status');
        $dryRun = (bool) $this->option('dry-run');

        $absolutePath = str_starts_with($fileArg, '/')
            ? $fileArg
            : base_path($fileArg);

        if (! is_file($absolutePath)) {
            $this->error("Fixture file not found: {$absolutePath}");

            return self::FAILURE;
        }

        $order = $this->resolveOrder($orderArg);
        if ($order === null) {
            $this->error("Order not found for identifier: {$orderArg}");

            return self::FAILURE;
        }

        $rows = $this->readFixture($absolutePath);
        if ($rows === []) {
            $this->warn('Fixture has no rows. Nothing to import.');

            return self::SUCCESS;
        }

        if ($dryRun) {
            $this->line("Dry run: {$order->order_code} (#{$order->id})");
            $this->line('Rows parsed: '.count($rows));
            $first = $rows[0];
            $this->line(sprintf(
                'Example row -> line_no=%d, lot_code=%s, qty=%s, uom=%s',
                $first['line_no'],
                $first['lot_code'] ?? '-',
                (string) $first['quantity'],
                $first['uom'] ?? '-'
            ));

            return self::SUCCESS;
        }

        $upserted = 0;
        DB::transaction(function () use ($rows, $order, $status, $procurementStatus, &$upserted): void {
            foreach ($rows as $row) {
                $payload = [
                    'lot_code' => $this->toNullableString($row['lot_code'] ?? null),
                    'name' => trim((string) ($row['name'] ?? '')),
                    'uom' => $this->toNullableString($row['uom'] ?? null),
                    'quantity' => $this->toDecimal($row['quantity'] ?? 0),
                    'project_location' => $this->toNullableString($row['project_location'] ?? null),
                    'required_delivery_timeline' => $this->toNullableString($row['required_delivery_timeline'] ?? null),
                    'proposed_delivery_timeline' => $this->toNullableString($row['proposed_delivery_timeline'] ?? null),
                    'status' => trim((string) ($row['status'] ?? $status)),
                    'procurement_status' => trim((string) ($row['procurement_status'] ?? $procurementStatus)),
                ];

                if ($payload['name'] === '') {
                    throw new RuntimeException('Row has empty "name".');
                }

                $order->items()->updateOrCreate(
                    ['line_no' => (int) $row['line_no']],
                    $payload
                );
                $upserted++;
            }
        });

        $this->info("Imported {$upserted} order items into {$order->order_code} (#{$order->id}).");

        return self::SUCCESS;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function readFixture(string $absolutePath): array
    {
        $decoded = json_decode((string) file_get_contents($absolutePath), true);
        if (! is_array($decoded) || ! array_is_list($decoded)) {
            throw new RuntimeException('Fixture must be a JSON array of rows.');
        }

        $rows = [];
        foreach ($decoded as $index => $row) {
            if (! is_array($row)) {
                continue;
            }
            $lineNo = isset($row['line_no']) ? (int) $row['line_no'] : ($index + 1);
            if ($lineNo <= 0) {
                $lineNo = $index + 1;
            }
            $row['line_no'] = $lineNo;
            $rows[] = $row;
        }

        return $rows;
    }

    private function resolveOrder(string $identifier): ?Order
    {
        if (is_numeric($identifier)) {
            $byId = Order::query()->find((int) $identifier);
            if ($byId !== null) {
                return $byId;
            }
        }

        return Order::query()->where('order_code', $identifier)->first();
    }

    private function toNullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $trimmed = trim((string) $value);

        return $trimmed === '' ? null : $trimmed;
    }

    private function toDecimal(mixed $value): float
    {
        $raw = trim((string) $value);
        if ($raw === '') {
            return 0.0;
        }

        $normalized = str_replace(' ', '', $raw);
        if (preg_match('/^\d{1,3}(\.\d{3})+(,\d+)?$/', $normalized) === 1) {
            $normalized = str_replace('.', '', $normalized);
            $normalized = str_replace(',', '.', $normalized);
        } elseif (preg_match('/^\d{1,3}(,\d{3})+(\.\d+)?$/', $normalized) === 1) {
            $normalized = str_replace(',', '', $normalized);
        } elseif (str_contains($normalized, ',') && ! str_contains($normalized, '.')) {
            $normalized = str_replace(',', '.', $normalized);
        }

        if (! is_numeric($normalized)) {
            throw new RuntimeException("Invalid numeric value: {$raw}");
        }

        return (float) $normalized;
    }
}
