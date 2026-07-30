<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\MembershipPlanRequest;
use App\Http\Resources\MembershipPlanResource;
use App\Services\MembershipPlanService;

class MembershipPlanController extends Controller
{
    protected MembershipPlanService $service;

    public function __construct(MembershipPlanService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        return MembershipPlanResource::collection(
            $this->service->getAll()
        );
    }

    public function store(MembershipPlanRequest $request)
    {
        $plan = $this->service->create(
            $request->validated()
        );

        return new MembershipPlanResource($plan);
    }

    public function show(string $id)
    {
        return new MembershipPlanResource(
            $this->service->find($id)
        );
    }

    public function update(MembershipPlanRequest $request, string $id)
    {
        return new MembershipPlanResource(
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
            'message' => 'Plan eliminado correctamente.'
        ]);
    }
}