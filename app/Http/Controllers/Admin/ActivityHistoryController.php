<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ActivityHistory\ActivityHistoryCollection;
use App\Models\ActivityHistory;
use Illuminate\Http\Request;

class ActivityHistoryController extends Controller
{
    public function get(Request $request)
    {
        $this->authorize('activities_view');

        $activities = ActivityHistory::whereDate('created_at', '>=', $request->start_date)
            ->whereDate('created_at', '<=', $request->end_date)
            ->where(function ($q) use ($request) {
                $q->where('action', 'Like', '%' . $request->keyword . '%')
                    ->orWhere('model', 'Like', '%' . $request->keyword . '%')
                    ->orWhere('platform', 'Like', '%' . $request->keyword . '%')
                    ->orWhere('browser', 'Like', '%' . $request->keyword . '%')
                    ->orWhereHas('user', function ($q) use ($request) {
                        $q->where('firstname', 'Like', '%' . $request->keyword . '%')
                            ->orWhere('lastname', 'Like', '%' . $request->keyword . '%')
                            ->orWhere('email', 'Like', '%' . $request->keyword . '%');
                    });
            })
            ->orderBy('created_at', 'DESC')
            ->paginate($request->per_page);

        return new ActivityHistoryCollection($activities);
    }
}
