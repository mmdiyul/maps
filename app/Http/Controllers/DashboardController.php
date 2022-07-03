<?php

namespace App\Http\Controllers;

use App\Models\Gis;

class DashboardController extends Controller
{
    public function index()
    {
        $data = Gis::all();
        foreach ($data as $item) {
            $item['geojson'] = Gis::convertGeomToGeoJson($item->geometry);
        }
        return view('dashboard')->with('data', $data);
    }
}
