<?php

namespace App\Http\Controllers;

use App\Models\DriveFile;
use Illuminate\Http\Request;

class DriveFileController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $request->validate([
            'files' => 'required|array',
            'files.*.id' => 'required|string',
            'files.*.name' => 'required|string',
            'files.*.mimeType' => 'required|string',
            'files.*.url' => 'required|string',
            'files.*.iconUrl' => 'nullable|string',
            'files.*.embedUrl' => 'nullable|string',
            'files.*.organizationDisplayName' => 'nullable|string',
            'files.*.isShared' => 'nullable|boolean',
        ]);

        foreach ($request->input('files') as $fileData) {
            DriveFile::updateOrCreate(
                ['file_id' => $fileData['id']],
                [
                    'name' => $fileData['name'],
                    'file_id' => $fileData['id'],
                    'mime_type' => $fileData['mimeType'],
                    'url' => $fileData['url'],
                    'icon_url' => $fileData['iconUrl'] ?? null,
                    'embed_url' => $fileData['embedUrl'] ?? null,
                    'organization_display_name' => $fileData['organizationDisplayName'] ?? null,
                    'is_shared' => $fileData['isShared'] ?? false,
                ]
            );
        }

        return redirect()->back()->with('success', 'Files uploaded successfully.');
    }
}