<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search'));
        $customers = Customer::query()
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

        return view('customers.index', compact('customers', 'search'));
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(Request $request)
    {
        Customer::create($this->validatedData($request));

        return redirect()
            ->route('customers.index')
            ->with('success', 'Customer created successfully.');
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    public function show(Customer $customer)
    {
        return view('customers.show', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $customer->update($this->validatedData($request, $customer));

        return redirect()
            ->route('customers.index')
            ->with('success', 'Customer updated successfully.');
    }

    public function destroy(Request $request, Customer $customer)
    {
        $this->authorizeDelete($request);

        if ($customer->ledgers()->exists()) {
            return back()->with('error', 'Cannot delete customer with ledger transactions.');
        }

        $customer->delete();

        return redirect()
            ->route('customers.index')
            ->with('success', 'Customer deleted successfully.');
    }

    private function validatedData(Request $request, ?Customer $customer = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30', Rule::unique('customers', 'phone')->ignore($customer)->withoutTrashed()],
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
