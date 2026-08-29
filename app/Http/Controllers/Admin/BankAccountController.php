<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BankAccountController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/BankAccounts', [
            'bankAccounts' => BankAccount::orderBy('bank_name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'bank_name' => ['required', 'string', 'max:255'],
            'account_number' => ['required', 'string', 'max:50'],
            'account_holder' => ['required', 'string', 'max:255'],
        ]);

        BankAccount::create($validated + ['is_active' => true]);

        return back()->with('status', 'bank-account-created');
    }

    public function update(Request $request, BankAccount $bankAccount): RedirectResponse
    {
        $validated = $request->validate([
            'bank_name' => ['required', 'string', 'max:255'],
            'account_number' => ['required', 'string', 'max:50'],
            'account_holder' => ['required', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ]);

        $bankAccount->update($validated);

        return back()->with('status', 'bank-account-updated');
    }

    public function destroy(BankAccount $bankAccount): RedirectResponse
    {
        $bankAccount->delete();

        return back()->with('status', 'bank-account-deleted');
    }
}
