<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\SubscriptionRequest;
use App\Http\Resources\SubscriptionResource;
use App\Services\SubscriptionService;
use App\Http\Controllers\Controller;

class SubscriptionController extends Controller
{
    protected SubscriptionService $service;
    
    public function __construct(SubscriptionService $service)
    {
        $this -> service = $service;
    }
    
   public function index()
{
    return SubscriptionResource::collection(
        $this->service->getAll()
    );
}
     public function store(SubscriptionRequest $request)
    {
        $plan = $this->service->create(
            $request->validated()
        );

        return new SubscriptionResource($plan);
    }

    public function show(int $id)
    {
        return new SubscriptionResource(
            $this->service->find($id)
        );
    }

    public function update(SubscriptionRequest $request, int $id)
    {
        return new SubscriptionResource(
            $this->service->update(
                $id,
                $request->validated()
            )
        );
    }

    public function destroy(string $id)
    {
        $this->service->delete($id);

        return response()->json([
            'message' => 'Suscripcion eliminada correctamente.'
        ]);
    }
}