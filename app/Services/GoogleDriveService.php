<?php

namespace App\Services;

use Google_Client;
use Google_Service_Drive;
use Google_Service_Drive_DriveFile;

class GoogleDriveService
{
    protected $client;
    protected $driveService;

    public function __construct()
    {
        $client = new Google_Client();
        $client->setAuthConfig(storage_path('app/google/credentials.json')); // Path to your credentials.json file
        $client->addScope(Google_Service_Drive::DRIVE);
        $client->setAccessType('offline');
        $client->setApprovalPrompt('force');

        $this->client = $client;
        $this->driveService = new Google_Service_Drive($this->client);
    }

    // Get Google Drive service
    public function getService()
    {
        return $this->driveService;
    }

    // Get file metadata by file ID
    public function getFile($fileId)
    {
        return $this->driveService->files->get($fileId);
    }

    // Download file from Google Drive
    public function downloadFile($fileId)
    {
        $response = $this->driveService->files->get($fileId, ['alt' => 'media']);
        return $response->getBody()->getContents();
    }

    // Upload a file to Google Drive
    public function uploadFile($filePath, $fileName, $mimeType = null)
    {
        $driveService = $this->getService();

        // Upload the file
        try {
            $fileMetadata = new Google_Service_Drive_DriveFile([
                'name' => $fileName,
                'parents' => [env('GOOGLE_DRIVE_FOLDER_ID')],
            ]);
            $content = file_get_contents($filePath);

            $file = $driveService->files->create($fileMetadata, [
                'data' => $content,
                'mimeType' => $mimeType,
                'uploadType' => 'multipart',
                'fields' => 'id, parents'
            ]);

            \Log::info('Uploaded file: '.$file->id.' in folder: '.json_encode($file->parents));
            \Log::info('GOOGLE_DRIVE_FOLDER_ID from env: ' . env('GOOGLE_DRIVE_FOLDER_ID'));

            $permission = new \Google_Service_Drive_Permission([
                'type' => 'anyone',
                'role' => 'reader',
            ]);
            $driveService->permissions->create($file->id, $permission);

            return $file->getId(); // File uploaded successfully, return file metadata (or file ID)
        } catch (\Exception $e) {
            \Log::error("Error uploading file: " . $e->getMessage());
            throw new \Exception("Error uploading file: " . $e->getMessage());
        }
    }

    public function deleteFile($fileId)
    {
        $driveService = $this->getService();

        try {
            $driveService->files->delete($fileId);
            return true;
        } catch (\Google_Service_Exception $e) {
            // If file not found (already deleted), ignore it
            if ($e->getCode() !== 404) {
                \Log::error("Google Drive delete error: " . $e->getMessage());
            }
            return false;
        } catch (\Exception $e) {
            \Log::error("General delete error: " . $e->getMessage());
            return false;
        }
    }
}