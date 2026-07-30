<?php

namespace App\Services;

use App\Models\MembershipPlan;

class MembershipPlanService
{
    public function getAll()
    {
        return MembershipPlan::all();
    }

    public function create(array $data)
    {
        return MembershipPlan::create($data);
    }

    public function find(int $id)
    {
        return MembershipPlan::findOrFail($id);
    }

    public function update(int $id, array $data)
    {
        $plan = MembershipPlan::findOrFail($id);

        $plan->update($data);

        return $plan;
    }

    public function delete(int $id)
    {
        $plan = MembershipPlan::findOrFail($id);

        return $plan->delete();
    }
}