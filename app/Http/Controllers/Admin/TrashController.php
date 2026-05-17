<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\City;
use App\Models\BusRoute;
use App\Models\Trip;
use App\Models\Bus;
use App\Models\BusType;
use App\Models\Terminal;

class TrashController extends Controller
{
    /**
     * Map of type strings to their corresponding Eloquent Models
     */
    protected $models = [
        'cities' => City::class,
        'routes' => BusRoute::class,
        'trips'  => Trip::class,
        'buses'  => Bus::class,
        'bus-types' => BusType::class,
        'terminals' => Terminal::class,
    ];

    public function index(Request $request)
    {
        $type = $request->query('type', 'cities');

        if (!array_key_exists($type, $this->models)) {
            abort(404, "Invalid trash type.");
        }

        $modelClass = $this->models[$type];
        
        // Auto-fix for terminals missing soft deletes column
        if ($type === 'terminals' && !\Illuminate\Support\Facades\Schema::hasColumn('terminals', 'deleted_at')) {
            \Illuminate\Support\Facades\Schema::table('terminals', function ($table) {
                $table->softDeletes();
            });
        }

        $query = $modelClass::onlyTrashed();

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            // Basic search handling depending on type
            if ($type === 'cities') {
                $query->where('name', 'like', "%{$search}%");
            } elseif ($type === 'routes') {
                $query->where('route_name', 'like', "%{$search}%");
            } elseif ($type === 'trips') {
                $query->where('trip_code', 'like', "%{$search}%");
            } elseif ($type === 'buses') {
                $query->where('bus_number', 'like', "%{$search}%");
            } elseif ($type === 'bus-types') {
                $query->where('type_name', 'like', "%{$search}%");
            } elseif ($type === 'terminals') {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%");
            }
        }

        $trashedItems = $query->paginate(15)->withQueryString();

        $counts = [];
        foreach ($this->models as $key => $model) {
            try {
                $counts[$key] = $model::onlyTrashed()->count();
            } catch (\Exception $e) {
                $counts[$key] = 0;
            }
        }

        return view('admin.trash.index', compact('trashedItems', 'type', 'counts'));
    }

    public function restore(Request $request, $type, $id)
    {
        if (!array_key_exists($type, $this->models)) {
            abort(404, "Invalid trash type.");
        }

        $modelClass = $this->models[$type];
        $item = $modelClass::onlyTrashed()->findOrFail($id);
        
        $item->restore();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Record restored successfully.']);
        }

        return redirect()->route('admin.trash.index', ['type' => $type])
                         ->with('success', 'Record restored successfully.');
    }

    public function forceDelete(Request $request, $type, $id)
    {
        if (!array_key_exists($type, $this->models)) {
            abort(404, "Invalid trash type.");
        }

        $modelClass = $this->models[$type];
        $item = $modelClass::onlyTrashed()->findOrFail($id);
        
        $item->forceDelete();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Record permanently deleted.']);
        }

        return redirect()->route('admin.trash.index', ['type' => $type])
                         ->with('success', 'Record permanently deleted.');
    }
}
