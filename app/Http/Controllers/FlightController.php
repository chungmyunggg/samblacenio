<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use App\Models\Flight;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FlightController extends Controller
{
    /**
     * Display the destinations with their last flight (Advanced Subquery).
     */
    public function index()
    {
        // Using subquery functionality to select all destinations and the name of
        // the flight that most recently arrived at that destination.
        return Destination::addSelect(['last_flight' => Flight::select('name')
            ->whereColumn('destination_id', 'destinations.id')
            ->orderByDesc('arrived_at')
            ->limit(1)
        ])->get();
    }

    /**
     * Store a new flight in the database.
     */
    public function store(Request $request): RedirectResponse
    {
        // Validate the request...
        
        // Mass Assignment: Create the flight using a single statement
        // Ensure 'name' and other fields are in the $fillable array in the model
        Flight::create($request->all());

        return redirect('/flights');
    }

    /**
     * Update or create a flight record.
     */
    public function update()
    {
        // Attempt to find a flight matching the first array, or create it with values from the second
        $flight = Flight::updateOrCreate(
            ['departure' => 'Oakland', 'destination' => 'San Diego'],
            ['price' => 99, 'discounted' => 1]
        );

        if ($flight->wasRecentlyCreated) {
            // New flight record was inserted...
        }
    }

    /**
     * Perform a bulk upsert operation.
     */
    public function bulkUpdate()
    {
        // Efficiently update or insert multiple records in a single query
        Flight::upsert([
            ['departure' => 'Oakland', 'destination' => 'San Diego', 'price' => 99],
            ['departure' => 'Chicago', 'destination' => 'New York', 'price' => 150]
        ], uniqueBy: ['departure', 'destination'], update: ['price']);
    }

    /**
     * Delete (soft delete) a flight.
     */
    public function destroy(string $id): RedirectResponse
    {
        Flight::destroy($id);
        return redirect('/flights');
    }

    /**
     * Restore a soft-deleted flight.
     */
    public function restore(string $id): RedirectResponse
    {
        Flight::withTrashed()->find($id)->restore();
        return redirect('/flights');
    }
}