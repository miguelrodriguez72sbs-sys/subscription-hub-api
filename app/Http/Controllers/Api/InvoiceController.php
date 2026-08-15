<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\InvoiceStatusRequest;
use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use App\Services\InvoiceService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    protected InvoiceService $service;

    public function __construct(InvoiceService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $invoices = $request->user()->isAdmin()
            ? $this->service->getAll()
            : $this->service->getAllForUser($request->user()->id);

        return InvoiceResource::collection($invoices);
    }

    public function show(Request $request, int $id)
    {
        $invoice = $this->service->find($id);

        abort_unless(
            $request->user()->isAdmin() || $this->service->belongsToUser($invoice, $request->user()->id),
            403,
            'No tienes permisos para ver esta factura.'
        );

        return new InvoiceResource($invoice);
    }

    public function pdf(Request $request, int $id)
    {
        $invoice = Invoice::with('subscription.user', 'subscription.membershipPlan')
            ->findOrFail($id);

        abort_unless(
            $request->user()->isAdmin() || $invoice->subscription->user_id === $request->user()->id,
            403,
            'No tienes permisos para ver esta factura.'
        );

        $pdf = Pdf::loadView('invoices.pdf', ['invoice' => $invoice])
            ->setPaper('a4');

        return $pdf->download('factura-' . str_pad($invoice->id, 6, '0', STR_PAD_LEFT) . '.pdf');
    }

    public function updateStatus(InvoiceStatusRequest $request, int $id)
    {
        $invoice = $this->service->updateStatus($id, $request->status);

        return response()->json([
            'message' => 'Estado de la factura actualizado correctamente.',
            'invoice' => new InvoiceResource($invoice),
        ]);
    }
}
