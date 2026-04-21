<?php

namespace App\Http\Controllers;

use App\Models\Ops\FounderWorkCard;
use App\Support\Ops\FilamentAccess;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class FounderDigestExportController extends Controller
{
    public function show(Request $request): View|Response
    {
        $user = $request->user();
        if ($user === null) {
            abort(Response::HTTP_UNAUTHORIZED);
        }

        abort_unless(FilamentAccess::isFounder(), Response::HTTP_FORBIDDEN);

        $open = FounderWorkCard::query()
            ->openForFounder($user->id)
            ->orderBy('due_at')
            ->get();

        $digestSignatureCount = $open->where('digest_lane', FounderWorkCard::LANE_SIGNATURE)->count();
        $digestReplyCount = $open->where('digest_lane', FounderWorkCard::LANE_REPLY)->count();
        $digestOverdueCount = $open->filter(fn (FounderWorkCard $c): bool => $c->isOverdue())->count();

        return view('founder.digest-export', [
            'digestSignatureCount' => $digestSignatureCount,
            'digestReplyCount' => $digestReplyCount,
            'digestOverdueCount' => $digestOverdueCount,
            'workCards' => $open,
        ]);
    }
}
