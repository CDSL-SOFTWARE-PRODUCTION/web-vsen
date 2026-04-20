<?php

namespace App\Console\Commands;

use App\Models\Demand\BidOpeningSession;
use App\Models\Demand\TenderSnapshot;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ImportBidOpeningCommand extends Command
{
    protected $signature = 'ops:bbmt-import
        {file : Path to CSV/JSON file}
        {--tbmt= : Notify number override}
        {--plan= : Plan number override}
        {--source_system=muasamcong : Source system code}
        {--opened_at= : Opened timestamp override}
        {--version=1 : Session version}
        {--create-snapshot : Create tender snapshot anchor when missing}';

    protected $description = 'Import bid opening matrix into sidecar tables (sessions + lines).';

    public function handle(): int
    {
        $path = (string) $this->argument('file');
        if (! is_file($path)) {
            $this->error("File not found: {$path}");

            return self::FAILURE;
        }

        $extension = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));
        $rows = match ($extension) {
            'json' => $this->readJsonRows($path),
            'csv' => $this->readCsvRows($path),
            default => throw new RuntimeException('Unsupported file type. Use CSV or JSON.'),
        };

        if ($rows === []) {
            $this->warn('No rows found. Nothing imported.');

            return self::SUCCESS;
        }

        $sourceSystem = (string) $this->option('source_system');
        $tbmt = (string) ($this->option('tbmt') ?? $rows[0]['tbmt'] ?? '');
        if ($tbmt === '') {
            throw new RuntimeException('TBMT is required. Provide --tbmt or include tbmt column.');
        }

        $planNo = $this->option('plan') !== null
            ? (string) $this->option('plan')
            : (string) ($rows[0]['source_plan_no'] ?? $rows[0]['plan_no'] ?? '');
        $openedAt = $this->normalizeDateTime((string) ($this->option('opened_at') ?? ($rows[0]['opened_at'] ?? '')));
        $sessionVersion = max(1, (int) $this->option('version'));

        $session = DB::transaction(function () use (
            $sourceSystem,
            $tbmt,
            $planNo,
            $openedAt,
            $sessionVersion,
            $rows
        ): BidOpeningSession {
            $snapshot = $this->resolveSnapshot($sourceSystem, $tbmt, $planNo);
            $session = BidOpeningSession::query()->firstOrCreate(
                [
                    'source_system' => $sourceSystem,
                    'source_notify_no' => $tbmt,
                    'session_version' => $sessionVersion,
                ],
                [
                    'tender_snapshot_id' => $snapshot?->id,
                    'source_plan_no' => $planNo !== '' ? $planNo : null,
                    'opened_at' => $openedAt,
                ]
            );

            $imported = 0;
            foreach ($rows as $index => $row) {
                $normalized = $this->normalizeRow($row, $index + 1);
                $fingerprint = $this->fingerprint($session->id, $normalized);

                $session->lines()->updateOrCreate(
                    [
                        'row_fingerprint' => $fingerprint,
                    ],
                    [
                        'source_row_no' => $normalized['source_row_no'],
                        'lot_code' => $normalized['lot_code'],
                        'item_name' => $normalized['item_name'],
                        'bidder_identifier' => $normalized['bidder_identifier'],
                        'bidder_name' => $normalized['bidder_name'],
                        'bid_valid_days' => $normalized['bid_valid_days'],
                        'bid_security_value' => $normalized['bid_security_value'],
                        'bid_security_days' => $normalized['bid_security_days'],
                        'bid_price' => $normalized['bid_price'],
                        'discount_rate' => $normalized['discount_rate'],
                        'bid_price_after_discount' => $normalized['bid_price_after_discount'],
                        'delivery_commitment' => $normalized['delivery_commitment'],
                        'currency' => $normalized['currency'],
                    ]
                );

                $imported++;
            }

            $session->forceFill([
                'total_bidders' => (int) $session->lines()
                    ->selectRaw('COUNT(DISTINCT COALESCE(NULLIF(bidder_identifier, \'\'), bidder_name)) as c')
                    ->value('c'),
                'raw_payload_hash' => hash('sha256', json_encode($rows, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)),
                'opened_at' => $openedAt ?? $session->opened_at,
                'source_plan_no' => $planNo !== '' ? $planNo : $session->source_plan_no,
            ])->save();

            $this->line("Imported rows: {$imported}");

            return $session->fresh();
        });

        $this->info("Session #{$session->id} imported for TBMT {$session->source_notify_no}.");

        return self::SUCCESS;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function readCsvRows(string $path): array
    {
        $rows = [];
        $handle = fopen($path, 'rb');
        if ($handle === false) {
            throw new RuntimeException("Cannot open file: {$path}");
        }

        $header = null;
        while (($data = fgetcsv($handle)) !== false) {
            if ($header === null) {
                $header = array_map(
                    static fn ($value): string => trim((string) $value),
                    $data
                );

                continue;
            }

            if ($data === [null] || $data === []) {
                continue;
            }

            /** @var array<string, mixed> $row */
            $row = [];
            foreach ($header as $index => $column) {
                if ($column === '') {
                    continue;
                }
                $row[$column] = isset($data[$index]) ? trim((string) $data[$index]) : null;
            }
            $rows[] = $row;
        }
        fclose($handle);

        return $rows;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function readJsonRows(string $path): array
    {
        $decoded = json_decode((string) file_get_contents($path), true);
        if (! is_array($decoded)) {
            throw new RuntimeException('JSON payload must be an array or object with lines.');
        }

        if (array_is_list($decoded)) {
            /** @var array<int, array<string, mixed>> $decoded */
            return $decoded;
        }

        $lines = $decoded['lines'] ?? [];
        if (! is_array($lines)) {
            throw new RuntimeException('JSON object format requires "lines" array.');
        }

        $meta = $decoded['meta'] ?? [];
        if (! is_array($meta)) {
            $meta = [];
        }

        $merged = [];
        foreach ($lines as $line) {
            if (! is_array($line)) {
                continue;
            }
            $merged[] = array_merge($meta, $line);
        }

        return $merged;
    }

    private function resolveSnapshot(string $sourceSystem, string $tbmt, string $planNo): ?TenderSnapshot
    {
        $snapshot = TenderSnapshot::query()
            ->where('source_system', $sourceSystem)
            ->where('source_notify_no', $tbmt)
            ->first();

        if ($snapshot !== null || ! (bool) $this->option('create-snapshot')) {
            return $snapshot;
        }

        return TenderSnapshot::query()->create([
            'source_system' => $sourceSystem,
            'source_notify_no' => $tbmt,
            'source_plan_no' => $planNo !== '' ? $planNo : null,
        ]);
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array{
     *     source_row_no: int|null,
     *     lot_code: string,
     *     item_name: string|null,
     *     bidder_identifier: string|null,
     *     bidder_name: string,
     *     bid_valid_days: int|null,
     *     bid_security_value: float|null,
     *     bid_security_days: int|null,
     *     bid_price: float,
     *     discount_rate: float,
     *     bid_price_after_discount: float|null,
     *     delivery_commitment: string|null,
     *     currency: string
     * }
     */
    private function normalizeRow(array $row, int $fallbackRowNo): array
    {
        $lotCode = trim((string) ($row['lot_code'] ?? $row['pp_code'] ?? ''));
        $bidderName = trim((string) ($row['bidder_name'] ?? ''));
        $bidPrice = $this->toFloat($row['bid_price_vnd'] ?? $row['bid_price'] ?? null);

        if ($lotCode === '' || $bidderName === '' || $bidPrice === null) {
            throw new RuntimeException('Each row requires lot_code, bidder_name, bid_price_vnd.');
        }

        $discountRate = $this->toFloat($row['discount_rate'] ?? $row['discount_pct'] ?? 0) ?? 0.0;
        $afterDiscount = $this->toFloat($row['bid_price_after_discount'] ?? null);

        return [
            'source_row_no' => $this->toInt($row['source_row_no'] ?? $fallbackRowNo),
            'lot_code' => $lotCode,
            'item_name' => $this->toNullableString($row['item_name'] ?? null),
            'bidder_identifier' => $this->toNullableString($row['bidder_tax_id'] ?? $row['bidder_identifier'] ?? null),
            'bidder_name' => $bidderName,
            'bid_valid_days' => $this->toInt($row['bid_valid_days'] ?? null),
            'bid_security_value' => $this->toFloat($row['bid_security_value'] ?? null),
            'bid_security_days' => $this->toInt($row['bid_security_days'] ?? null),
            'bid_price' => $bidPrice,
            'discount_rate' => $discountRate,
            'bid_price_after_discount' => $afterDiscount,
            'delivery_commitment' => $this->toNullableString($row['delivery_commitment'] ?? $row['delivery_time'] ?? null),
            'currency' => strtoupper((string) ($row['currency'] ?? 'VND')),
        ];
    }

    /**
     * @param  array{
     *     source_row_no: int|null,
     *     lot_code: string,
     *     item_name: string|null,
     *     bidder_identifier: string|null,
     *     bidder_name: string,
     *     bid_valid_days: int|null,
     *     bid_security_value: float|null,
     *     bid_security_days: int|null,
     *     bid_price: float,
     *     discount_rate: float,
     *     bid_price_after_discount: float|null,
     *     delivery_commitment: string|null,
     *     currency: string
     * } $row
     */
    private function fingerprint(int $sessionId, array $row): string
    {
        $parts = [
            (string) $sessionId,
            $row['lot_code'],
            $row['bidder_identifier'] ?? '',
            $row['bidder_name'],
            number_format($row['bid_price'], 2, '.', ''),
            number_format($row['discount_rate'], 4, '.', ''),
            $row['currency'],
        ];

        return hash('sha256', implode('|', $parts));
    }

    private function toNullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $trimmed = trim((string) $value);

        return $trimmed === '' ? null : $trimmed;
    }

    private function toFloat(mixed $value): ?float
    {
        if ($value === null) {
            return null;
        }
        $raw = trim((string) $value);
        if ($raw === '') {
            return null;
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
            return null;
        }

        return (float) $normalized;
    }

    private function toInt(mixed $value): ?int
    {
        $float = $this->toFloat($value);
        if ($float === null) {
            return null;
        }

        return (int) round($float);
    }

    private function normalizeDateTime(string $value): ?Carbon
    {
        $trimmed = trim($value);
        if ($trimmed === '') {
            return null;
        }

        return Carbon::parse($trimmed);
    }
}
