<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use App\Models\Flight;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
            ->orderByDesc('created_at')
            ->limit(1)
        ])->get();
    }

    /**
     * Store a new flight in the database.
     */
    public function store(Request $request): JsonResponse
    {
        // Validate the request...
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'destination_id' => 'nullable|integer|exists:destinations,id',
            'price' => 'nullable|numeric',
            'departure' => 'nullable|string',
            'attachment' => 'nullable|file|max:10240',
        ]);

        $attachmentInfo = null;

        // Handle file upload if present
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            // Store file using the store method on the UploadedFile instance
            // This corresponds to the "File Uploads" section
            $path = $file->store('flight-attachments');

            if ($path) {
                // Retrieve Metadata as per "File Metadata" section
                $attachmentInfo = [
                    'path' => $path,
                    'size' => Storage::size($path),
                    'mime_type' => Storage::mimeType($path),
                    'last_modified' => Storage::lastModified($path),
                ];
            }

            // Remove attachment from validated data to prevent model mass-assignment errors
            unset($validated['attachment']);
        }

        // Mass Assignment: Create the flight using a single statement
        // Ensure 'name' and other fields are in the $fillable array in the model
        $flight = Flight::create($validated);

        if ($attachmentInfo) {
            $flight->attachment_info = $attachmentInfo;
            $flight->save();
        }

        return response()->json($flight, 201);
    }

    /**
     * Delete (soft delete) a flight.
     */
    public function destroy(string $id): JsonResponse
    {
        Flight::destroy($id);
        return response()->json(['message' => 'Flight deleted successfully']);
    }

    /**
     * Restore a soft-deleted flight.
     */
    public function restore(string $id): JsonResponse
    {
        Flight::withTrashed()->findOrFail($id)->restore();
        return response()->json(['message' => 'Flight restored successfully']);
    }

    /**
     * Get a temporary download URL for a flight attachment.
     * Corresponds to "Temporary URLs" section.
     */
    public function getAttachmentLink(Request $request): JsonResponse
    {
        $request->validate(['path' => 'required|string']);
        $path = $request->input('path');

        // Create a temporary URL valid for 5 minutes
        $url = Storage::temporaryUrl(
            $path,
            now()->addMinutes(5)
        );

        return response()->json(['url' => $url]);
    }

    /**
     * Get a temporary upload URL for direct client uploads.
     * Corresponds to "Temporary Upload URLs" section (s3/local drivers).
     */
    public function getUploadLink(Request $request): JsonResponse
    {
        $request->validate(['filename' => 'required|string']);

        ['url' => $url, 'headers' => $headers] = Storage::temporaryUploadUrl(
            $request->input('filename'),
            now()->addMinutes(5)
        );

        return response()->json(['upload_url' => $url, 'headers' => $headers]);
    }

    /**
     * List all files in the flight attachments directory.
     * Corresponds to "Get All Files Within a Directory".
     */
    public function listAttachments(): JsonResponse
    {
        // Get files in directory
        $files = Storage::files('flight-attachments');
        // Get all files recursively
        $allFiles = Storage::allFiles('flight-attachments');

        return response()->json(['files' => $files, 'all_files' => $allFiles]);
    }

    /**
     * Clean up and recreate the attachments directory.
     * Corresponds to "Directories" (makeDirectory, deleteDirectory).
     */
    public function refreshAttachmentsDirectory(): JsonResponse
    {
        // Delete directory and all contents
        Storage::deleteDirectory('flight-attachments');

        // Create directory again
        Storage::makeDirectory('flight-attachments');

        return response()->json(['message' => 'Attachments directory refreshed']);
    }
}