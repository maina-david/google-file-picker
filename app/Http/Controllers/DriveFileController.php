<?php

namespace App\Http\Controllers;

use App\Models\DriveFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Google\Client;
use Google\Service\Drive;
use Illuminate\Support\Facades\Log;
use Throwable;

class DriveFileController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        logger($request->all());
        $request->validate([
            'access_token' => 'required',
            'files' => 'required|array',
            'files.*.id' => 'required|string',
            'files.*.name' => 'required|string',
            'files.*.mimeType' => 'required|string',
        ]);

        foreach ($request->input('files') as $fileData) {
            $fileId = $fileData['id'];
            $fileName = $fileData['name'];
            $mimeType = $fileData['mimeType'];

            $fileContent = $this->downloadGoogleFile($fileId, $request->access_token);

            if ($fileContent !== false) {
                $localFilePath = "drive-files/{$fileName}";

                Storage::put($localFilePath, $fileContent);

                DriveFile::updateOrCreate(
                    ['file_id' => $fileId],
                    [
                        'name' => $fileName,
                        'file_id' => $fileId,
                        'mime_type' => $mimeType,
                        'url' => Storage::url($localFilePath),
                        'is_shared' => $fileData['isShared'] ?? false,
                    ]
                );
            } else {
                return redirect()->back()->withErrors(['error' => 'Failed to fetch file from Google Drive.']);
            }
        }

        return redirect()->back()->with('success', 'Files uploaded successfully.');
    }

    /**
     * Download file from Google Drive using the Drive API client.
     */
    protected function downloadGoogleFile(string $fileId, string $accessToken)
    {
        try {
            $client = new Client();
            $client->setDeveloperKey(config('services.google.api_key'));
            $client->setAccessToken($accessToken);
            $client->addScope(Drive::DRIVE);

            $driveService = new Drive($client);

            /** @var \Psr\Http\Message\ResponseInterface $response */
            $response = $driveService->files->get($fileId, [
                'alt' => 'media',
                'supportsAllDrives' => true
            ]);

            return $response->getBody()->getContents();
        } catch (Throwable $th) {
            Log::error("Error fetching file ID {$fileId}: " . $th->getMessage());
            return false;
        }
    }

    protected function exportGoogleWorkspaceDocument(string $fileType, string $fileId, string $accessToken)
    {
        try {
            $client = new Client();
            $client->setDeveloperKey(config('services.google.api_key'));
            $client->setAccessToken($accessToken);
            $client->addScope(Drive::DRIVE);

            $driveService = new Drive($client);

            /** @var \Psr\Http\Message\ResponseInterface $response */
            $response = $driveService->files->export($fileId, $fileType, array(
                'alt' => 'media'
            ));

            $content = $response->getBody()->getContents();

            return $content;
        } catch (\Throwable $th) {
            Log::error("Error fetching file ID {$fileId}: " . $th->getMessage());
            return false;
        }
    }
}
