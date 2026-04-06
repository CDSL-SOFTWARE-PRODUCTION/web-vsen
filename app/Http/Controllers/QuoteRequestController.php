<?php

namespace App\Http\Controllers;

use App\Models\Cms\QuoteRequest;
use App\Models\Cms\CmsProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class QuoteRequestController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'nullable|exists:products,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'company' => 'nullable|string|max:255',
            'quantity' => 'nullable|integer|min:1',
            'message' => 'nullable|string',
        ]);

        QuoteRequest::create([
            'product_id' => $validated['product_id'] ?? null,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'company' => $validated['company'],
            'quantity' => $validated['quantity'] ?? 1,
            'message' => $validated['message'],
            'status' => 'new',
        ]);

        return redirect()->back()->with('success', __('Your quote request has been sent successfully! We will contact you shortly.'));
    }
}
