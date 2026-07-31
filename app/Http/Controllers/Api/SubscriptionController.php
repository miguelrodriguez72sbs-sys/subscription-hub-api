<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\SubscriptionRequest;
use App\Http\Resources\SubscriptionResource;
use App\Services\SubscriptionService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    protected SubscriptionService $service;
    
    public function __construct(SubscriptionService $service)
    {
        $this -> service = $service;
    }
    
   public function index(Request $request)
{
    $subscriptions = $request->user()->isAdmin()
        ? $this->service->getAll()
        : $this->service->getAllForUser($request->user()->id);

    return SubscriptionResource::collection($subscriptions);
}
     public function store(SubscriptionRequest $request)
    {
        $data = $request->validated();

        if (! $request->user()->isAdmin()) {
            $data['user_id'] = $request->user()->id;
        }

        $plan = $this->service->create($data);

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