<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RespondsToAjax;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    use RespondsToAjax;

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

        return $this->successResponse($request, 'Customer created successfully.', route('customers.index'));
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    public function show(Customer $customer)
    {
        return view('customers.show', compact('customer'));
    }

    public function lookup(Customer $customer)
    {
        return response()->json([
            'success' => true,
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'phone' => $customer->phone,
                'email' => $customer->email,
                'gst_number' => $customer->gst_number,
                'address' => $customer->address,
                'current_balance' => (float) $customer->current_balance,
                'status' => $customer->status,
            ],
        ]);
    }

    public function update(Request $request, Customer $customer)
    {
        $customer->update($this->validatedData($request, $customer));

        return $this->successResponse($request, 'Customer updated successfully.', route('customers.index'));
    }

    public function destroy(Request $request, Customer $customer)
    {
        $this->authorizeDelete($request);

        if ($customer->ledgers()->exists()) {
            if ($request->expectsJson() || $request->ajax()) {
                return $this->errorResponse($request, 'Cannot delete customer with ledger transactions.', 409);
            }

            return back()->with('error', 'Cannot delete customer with ledger transactions.');
        }

        $customer->delete();

        return $this->successResponse($request, 'Customer deleted successfully.', route('customers.index'));
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
