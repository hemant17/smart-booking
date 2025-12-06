<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WorkingRule;
use Illuminate\Http\Request;

class WorkingRuleController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:weekly,date',
            'weekday' => 'required_if:type,weekly|nullable|integer|min:1|max:7',
            'date' => 'required_if:type,date|nullable|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'slot_interval' => 'required|integer|min:1',
            'active' => 'boolean'
        ]);

        $rule = WorkingRule::create([
            'type' => $request->type,
            'weekday' => $request->type === 'weekly' ? $request->weekday : null,
            'date' => $request->type === 'date' ? $request->date : null,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'slot_interval' => $request->slot_interval,
            'active' => $request->boolean('active', true)
        ]);

        return response()->json($rule, 201);
    }
}
