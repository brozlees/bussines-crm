<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeadReviewController extends Controller
{
    public function index(Request $request): View
    {
        $query = Lead::with(['listing.source', 'client', 'decidedBy'])
            ->orderByDesc('relevance_score')
            ->orderByDesc('created_at');

        $status = $request->query('status');
        if ($status) {
            $query->where('status', $status);
        } else {
            $query->whereIn('status', [Lead::STATUS_DRAFT, Lead::STATUS_APPROVED]);
        }

        if ($type = $request->query('type')) {
            $query->where('type', $type);
        }

        if ($deliveryMode = $request->query('delivery_mode')) {
            $query->where('delivery_mode', $deliveryMode);
        }

        $leads = $query->paginate(25)->withQueryString();

        return view('leads.index', compact('leads'));
    }

    public function show(Lead $lead): View
    {
        $lead->load(['listing.source', 'client', 'conversation.messages', 'decidedBy', 'messages']);

        return view('leads.show', compact('lead'));
    }

    public function approve(Lead $lead): RedirectResponse
    {
        if ($lead->status !== Lead::STATUS_DRAFT) {
            return back()->with('error', 'Одобрить можно только черновик.');
        }

        $lead->update([
            'status' => Lead::STATUS_APPROVED,
            'approved_at' => now(),
            'decided_by_user_id' => auth()->id(),
        ]);

        return back()->with('success', 'Лид одобрен.');
    }

    public function reject(Lead $lead): RedirectResponse
    {
        if (! $lead->isPending()) {
            return back()->with('error', 'Отклонить можно только лид в статусе черновик или одобрен.');
        }

        $lead->update([
            'status' => Lead::STATUS_REJECTED,
            'decided_by_user_id' => auth()->id(),
        ]);

        return back()->with('success', 'Лид отклонён.');
    }

    public function markAsSent(Lead $lead): RedirectResponse
    {
        if ($lead->delivery_mode !== Lead::DELIVERY_MANUAL) {
            return back()->with('error', 'Этот лид не требует ручной отметки.');
        }

        if ($lead->status !== Lead::STATUS_APPROVED) {
            return back()->with('error', 'Сначала одобрите лид.');
        }

        $lead->update([
            'status' => Lead::STATUS_SENT,
            'sent_at' => now(),
        ]);

        $lead->communicationLogs()->create([
            'client_id' => $lead->client_id,
            'conversation_id' => $lead->conversation_id,
            'channel' => $lead->channel ?? 'manual',
            'direction' => 'outbound',
            'content' => $lead->proposal_text,
            'status' => 'sent',
            'sent_by_user_id' => auth()->id(),
            'sent_at' => now(),
        ]);

        return back()->with('success', 'Лид отмечен как отправленный вручную.');
    }

    public function bulkApprove(Request $request): RedirectResponse
    {
        $ids = $request->input('lead_ids', []);

        $count = Lead::whereIn('id', $ids)
            ->where('status', Lead::STATUS_DRAFT)
            ->update([
                'status' => Lead::STATUS_APPROVED,
                'approved_at' => now(),
                'decided_by_user_id' => auth()->id(),
            ]);

        return back()->with('success', "Одобрено лидов: {$count}");
    }

    public function bulkReject(Request $request): RedirectResponse
    {
        $ids = $request->input('lead_ids', []);

        $count = Lead::whereIn('id', $ids)
            ->whereIn('status', [Lead::STATUS_DRAFT, Lead::STATUS_APPROVED])
            ->update([
                'status' => Lead::STATUS_REJECTED,
                'decided_by_user_id' => auth()->id(),
            ]);

        return back()->with('success', "Отклонено лидов: {$count}");
    }
}