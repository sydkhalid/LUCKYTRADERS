<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SupplierController extends Controller
{
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

        return redirect()
            ->route('suppliers.index')
            ->with('success', 'Supplier created successfully.');
    }

    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function show(Supplier $supplier)
    {
        return view('suppliers.show', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $supplier->update($this->validatedData($request, $supplier));

        return redirect()
            ->route('suppliers.index')
            ->with('success', 'Supplier updated successfully.');
    }

    public function destroy(Request $request, Supplier $supplier)
    {
        $this->authorizeDelete($request);

        if ($supplier->ledgers()->exists()) {
            return back()->with('error', 'Cannot delete supplier with ledger transactions.');
        }

        $supplier->delete();

        return redirect()
            ->route('suppliers.index')
            ->with('success', 'Supplier deleted successfully.');
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
