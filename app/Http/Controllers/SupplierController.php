<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RespondsToAjax;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SupplierController extends Controller
{
    use RespondsToAjax;

    public function index(Request $request)
    {
        $search = trim((string) $request->query('search'));
        $suppliers = Supplier::query()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', '%'.$search.'%')
                        ->orWhere('phone', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%')
                        ->orWhere('gst_number', 'like', '%'.$search.'%')
                        ->orWhere('status', 'like', '%'.$search.'%');
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('suppliers.index', compact('suppliers', 'search'));
    }

    public function create()
    {
        return view('suppliers.create');
    }

    public function store(Request $request)
    {
        Supplier::create($this->validatedData($request));

        return $this->successResponse($request, 'Supplier created successfully.', route('suppliers.index'));
    }

    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function show(Supplier $supplier)
    {
        return view('suppliers.show', compact('supplier'));
    }

    public function lookup(Supplier $supplier)
    {
        return response()->json([
            'success' => true,
            'supplier' => [
                'id' => $supplier->id,
                'name' => $supplier->name,
                'phone' => $supplier->phone,
                'email' => $supplier->email,
                'gst_number' => $supplier->gst_number,
                'address' => $supplier->address,
                'current_balance' => (float) $supplier->current_balance,
                'status' => $supplier->status,
            ],
        ]);
    }

    public function update(Request $request, Supplier $supplier)
    {
        $supplier->update($this->validatedData($request, $supplier));

        return $this->successResponse($request, 'Supplier updated successfully.', route('suppliers.index'));
    }

    public function destroy(Request $request, Supplier $supplier)
    {
        $this->authorizeDelete($request);

        if ($supplier->ledgers()->exists()) {
            if ($request->expectsJson() || $request->ajax()) {
                return $this->errorResponse($request, 'Cannot delete supplier with ledger transactions.', 409);
            }

            return back()->with('error', 'Cannot delete supplier with ledger transactions.');
        }

        $supplier->delete();

        return $this->successResponse($request, 'Supplier deleted successfully.', route('suppliers.index'));
    }

    private function validatedData(Request $request, ?Supplier $supplier = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30', Rule::unique('suppliers', 'phone')->ignore($supplier)->withoutTrashed()],
            'email' => ['nullable', 'email', 'max:255'],
            'gst_number' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'opening_balance' => ['required', 'numeric', 'min:0'],
            'balance_type' => ['required', Rule::in(['debit', 'credit'])],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);
    }

    private function authorizeDelete(Request $request): void
    {
        abort_unless($request->user()?->can('delete_records'), 403);
    }
}
