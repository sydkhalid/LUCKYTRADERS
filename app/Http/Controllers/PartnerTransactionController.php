<?php

namespace App\Http\Controllers;

use App\Http\Requests\Partners\StorePartnerTransactionRequest;
use App\Models\Partner;
use App\Models\PartnerTransaction;
use App\Services\PartnerPostingService;
use Illuminate\Http\Request;

class PartnerTransactionController extends Controller
{
    public function index(Partner $partner)
    {
        $transactions = $partner->transactions()
            ->latest('transaction_date')
            ->latest('id')
            ->paginate(30);

        return view('partners.transactions', compact('partner', 'transactions'));
    }

    public function create(Request $request, Partner $partner)
    {
        $transactionType = $request->query('transaction_type', $request->route('transaction_type', 'investment'));

        if (! array_key_exists($transactionType, PartnerTransaction::TYPES)) {
            abort(404);
        }

        return view('partners.transaction-create', [
            'partner' => $partner,
            'transactionType' => $transactionType,
            'title' => PartnerTransaction::TYPES[$transactionType],
        ]);
    }

    public function store(StorePartnerTransactionRequest $request, Partner $partner, PartnerPostingService $postingService)
    {
        $postingService->recordTransaction($partner, $request->validated());

        return redirect()
            ->route('partners.show', $partner)
            ->with('success', 'Partner transaction saved successfully.');
    }
}
