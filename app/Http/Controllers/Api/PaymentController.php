<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentResource;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    protected PaymentService $service;

    public function __construct(PaymentService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $payments = $request->user()->isAdmin()
            ? Payment::with('invoice.subscription')->get()
            : Payment::whereHas('invoice.subscription', function ($query) use ($request) {
                $query->where('user_id', $request->user()->id);
            })->with('invoice.subscription')->get();

        return PaymentResource::collection($payments);
    }

    public function show(Request $request, int $id)
    {
        $payment = Payment::with('invoice.subscription')->findOrFail($id);

        $belongsToUser = $payment->invoice->subscription->user_id === $request->user()->id;

        abort_unless(
            $request->user()->isAdmin() || $belongsToUser,
            403,
            'No tienes permisos para ver este pago.'
        );

        return new PaymentResource($payment);
    }

    public function store(Request $request)
    {
        $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'simulate_decision' => 'nullable|in:approved,declined',
        ]);

        $invoice = Invoice::findOrFail($request->invoice_id);

        $belongsToUser = $invoice->subscription->user_id === $request->user()->id;

        abort_unless(
            $request->user()->isAdmin() || $belongsToUser,
            403,
            'No tienes permisos para pagar esta factura.'
        );

        abort_if($invoice->status === 'paid', 422, 'La factura ya fue pagada.');

        $payment = $this->service->process($invoice, $request->input('simulate_decision'));

        return new PaymentResource($payment);
    }
}
