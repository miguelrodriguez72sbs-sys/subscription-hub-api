<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\InvoiceStatusRequest;
use App\Http\Resources\InvoiceResource;
use App\Services\InvoiceService;
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

    public function updateStatus(InvoiceStatusRequest $request, int $id)
    {
        $invoice = $this->service->updateStatus($id, $request->status);

        return response()->json([
            'message' => 'Estado de la factura actualizado correctamente.',
            'invoice' => new InvoiceResource($invoice),
        ]);
    }
}
