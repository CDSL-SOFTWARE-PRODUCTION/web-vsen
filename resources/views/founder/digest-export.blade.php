<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('ops.founder_inbox.digest_export.document_title') }}</title>
    <style>
        body { font-family: ui-sans-serif, system-ui, sans-serif; margin: 24px; color: #111; }
        h1 { font-size: 1.25rem; margin: 0 0 16px; }
        .digest { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 24px; }
        .digest div { border: 1px solid #ddd; padding: 12px; border-radius: 6px; }
        .digest dt { font-size: 0.75rem; color: #555; margin: 0; }
        .digest dd { font-size: 1.5rem; font-weight: 600; margin: 4px 0 0; }
        table { width: 100%; border-collapse: collapse; font-size: 0.875rem; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; vertical-align: top; }
        th { background: #f5f5f5; }
        .hint { font-size: 0.75rem; color: #666; margin-top: 24px; }
        @media print {
            body { margin: 12px; }
            .hint { display: none; }
        }
    </style>
</head>
<body>
    <h1>{{ __('ops.founder_inbox.digest_export.heading') }}</h1>
    <p>{{ now()->timezone(config('app.timezone'))->format('Y-m-d H:i') }}</p>

    <dl class="digest">
        <div>
            <dt>{{ __('ops.founder_inbox.digest.signature_heading') }}</dt>
            <dd>{{ $digestSignatureCount }}</dd>
        </div>
        <div>
            <dt>{{ __('ops.founder_inbox.digest.reply_heading') }}</dt>
            <dd>{{ $digestReplyCount }}</dd>
        </div>
        <div>
            <dt>{{ __('ops.founder_inbox.digest.overdue_heading') }}</dt>
            <dd>{{ $digestOverdueCount }}</dd>
        </div>
    </dl>

    <h2 style="font-size:1rem;margin:16px 0 8px;">{{ __('ops.founder_inbox.cards_heading') }}</h2>
    @if ($workCards->isEmpty())
        <p>{{ __('ops.founder_inbox.empty') }}</p>
    @else
        <table>
            <thead>
            <tr>
                <th>{{ __('ops.founder_inbox.digest_export.table.title') }}</th>
                <th>{{ __('ops.founder_inbox.digest_export.table.assignee') }}</th>
                <th>{{ __('ops.founder_inbox.digest_export.table.due') }}</th>
                <th>{{ __('ops.founder_inbox.digest_export.table.lane') }}</th>
                <th>{{ __('ops.founder_inbox.digest_export.table.summary') }}</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($workCards as $card)
                <tr>
                    <td>{{ $card->title }}</td>
                    <td>{{ $card->assignee_label ?? '—' }}</td>
                    <td>{{ $card->due_at?->timezone(config('app.timezone'))->format('Y-m-d H:i') ?? '—' }}</td>
                    <td>{{ __('ops.founder_inbox.card.lane.'.$card->digest_lane) }}</td>
                    <td>{{ $card->summary ?? '—' }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif

    <p class="hint">{{ __('ops.founder_inbox.digest_export.print_hint') }}</p>
</body>
</html>
