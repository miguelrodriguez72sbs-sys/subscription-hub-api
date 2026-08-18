<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\InvoiceStatusRequest;
use App\Http\Resources\InvoiceResource;
use App\Services\InvoicePdfService;
use App\Services\InvoiceService;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function __construct(
        protected InvoiceService $service,
        protected InvoicePdfService $pdfService
    ) {}

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

    public function pdf(Request $request, int $id)
    {
        $invoice = $this->service->findForPdf($id);

        abort_unless(
            $request->user()->isAdmin() || $this->service->belongsToUser($invoice, $request->user()->id),
            403,
            'No tienes permisos para descargar esta factura.'
        );

        $content = $this->pdfService->render($invoice);
        $filename = sprintf('factura-SH-%06d.pdf', $invoice->id);

        return response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Content-Length' => (string) strlen($content),
            'Cache-Control' => 'private, no-store, max-age=0',
        ]);
    }
}
