<?php

namespace App\Http\Controllers;

use App\Models\ChatHistory;
use App\Models\User;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class AdminController extends Controller
{
    private const FOLDERS_META_FILE = 'admin/supabase-year-folders.json';

    public function dashboard(Request $request)
    {
        $totalStudents = User::where('role', 'user')->count();
        $totalQuestions = Schema::hasTable('chat_histories') ? ChatHistory::count() : 0;

        $storageWarning = null;

        try {
            $folders = $this->loadFolders();
        } catch (\Throwable $exception) {
            Log::warning('Khong the tai danh sach folder Supabase', [
                'error' => $exception->getMessage(),
            ]);
            $folders = [];
            $storageWarning = 'Khong the tai danh sach folder tu Supabase. Vui long kiem tra cau hinh va ket noi.';
        }

        $selectedFolderKey = (string) $request->query('folder', '');
        $selectedFolder = collect($folders)->firstWhere('key', $selectedFolderKey);

        if ($selectedFolder === null && !empty($folders)) {
            $selectedFolder = $folders[0];
            $selectedFolderKey = (string) $selectedFolder['key'];
        }

        $folderFiles = [];

        if ($selectedFolder !== null) {
            try {
                $folderFiles = $this->listFolderFiles($selectedFolderKey);
            } catch (\Throwable $exception) {
                Log::warning('Khong the tai danh sach file Supabase', [
                    'error' => $exception->getMessage(),
                    'folder' => $selectedFolderKey,
                ]);
                $storageWarning = 'Khong the tai danh sach tep trong folder da chon.';
            }
        }

        return view('admin.dashboard', compact(
            'totalStudents',
            'totalQuestions',
            'folders',
            'selectedFolder',
            'folderFiles',
            'storageWarning'
        ));
    }

    public function storeFolder(Request $request)
    {
        $validated = $request->validate([
            'folder_name' => ['required', 'string', 'max:120'],
        ]);

        $folderName = trim((string) $validated['folder_name']);
        $folders = $this->loadFolders();
        $folderKey = $this->buildFolderKey($folderName);

        $duplicate = collect($folders)->contains(function ($folder) use ($folderKey, $folderName) {
            $existingKey = trim((string) ($folder['key'] ?? ''));
            $existingName = Str::lower(trim((string) ($folder['name'] ?? '')));

            return $existingKey === $folderKey || $existingName === Str::lower($folderName);
        });

        if ($duplicate) {
            return back()->withInput()->withErrors([
                'folder_name' => 'Folder nam hoc da ton tai tren Supabase. Vui long dung ten khac.',
            ]);
        }

        try {
            $this->uploadObject($folderKey . '/.keep', '', 'text/plain', true);
        } catch (\Throwable $exception) {
            Log::error('Tao folder Supabase that bai', [
                'error' => $exception->getMessage(),
                'folder' => $folderKey,
            ]);

            return back()->withInput()->withErrors([
                'folder_name' => 'Khong the tao folder tren Supabase: ' . $exception->getMessage(),
            ]);
        }

        $folders[] = [
            'key' => $folderKey,
            'name' => $folderName,
            'created_at' => now()->toISOString(),
        ];

        $this->saveFolders($folders);

        return redirect()->route('admin.dashboard', ['folder' => $folderKey])
            ->with('success', 'Da tao folder nam hoc thanh cong.');
    }

    public function updateFolder(Request $request, string $folderKey)
    {
        $validated = $request->validate([
            'folder_name' => ['required', 'string', 'max:120'],
        ]);

        $newFolderName = trim((string) $validated['folder_name']);
        $newFolderKey = $this->buildFolderKey($newFolderName);

        $folders = $this->loadFolders();
        $targetIndex = null;

        foreach ($folders as $index => $folder) {
            if (($folder['key'] ?? '') === $folderKey) {
                $targetIndex = $index;
                break;
            }
        }

        if ($targetIndex === null) {
            return back()->withErrors([
                'folder_name' => 'Khong tim thay folder can cap nhat.',
            ]);
        }

        $targetFolder = $folders[$targetIndex];
        $currentFolderKey = (string) ($targetFolder['key'] ?? '');

        $duplicate = collect($folders)
            ->except($targetIndex)
            ->contains(function ($folder) use ($newFolderKey, $newFolderName) {
                $existingKey = trim((string) ($folder['key'] ?? ''));
                $existingName = Str::lower(trim((string) ($folder['name'] ?? '')));

                return $existingKey === $newFolderKey || $existingName === Str::lower($newFolderName);
            });

        if ($duplicate) {
            return back()->withErrors([
                'folder_name' => 'Folder nam hoc da ton tai tren Supabase. Vui long dung ten khac.',
            ]);
        }

        if ($newFolderKey !== $currentFolderKey) {
            try {
                $this->renameFolderOnSupabase($currentFolderKey, $newFolderKey);
            } catch (\Throwable $exception) {
                Log::error('Doi ten folder tren Supabase that bai', [
                    'error' => $exception->getMessage(),
                    'source' => $currentFolderKey,
                    'destination' => $newFolderKey,
                ]);

                return back()->withErrors([
                    'folder_name' => 'Khong the doi ten folder tren Supabase: ' . $exception->getMessage(),
                ]);
            }
        }

        $folders[$targetIndex]['key'] = $newFolderKey;
        $folders[$targetIndex]['name'] = $newFolderName;
        $this->saveFolders($folders);

        return redirect()->route('admin.dashboard', ['folder' => $newFolderKey])
            ->with('success', 'Da cap nhat folder va dong bo len Supabase.');
    }

    public function uploadDocument(Request $request)
    {
        $validated = $request->validate([
            'folder_key' => ['required', 'string', 'max:120'],
            'documentFiles' => ['required', 'array', 'min:1'],
            'documentFiles.*' => ['required', 'file', 'mimes:pdf,docx,txt', 'max:30720'],
        ], [
            'documentFiles.required' => 'Vui long chon it nhat 1 tep.',
            'documentFiles.array' => 'Danh sach tep tai len khong hop le.',
            'documentFiles.min' => 'Vui long chon it nhat 1 tep.',
            'documentFiles.*.max' => 'Moi tep khong duoc vuot qua gioi han 30MB.',
            'documentFiles.*.mimes' => 'Chi ho tro dinh dang PDF, DOCX, TXT.',
        ]);

        $folders = $this->loadFolders();
        $folder = collect($folders)->firstWhere('key', (string) $validated['folder_key']);

        if ($folder === null) {
            return back()->withErrors([
                'documentFiles' => 'Folder nam hoc khong hop le.',
            ]);
        }

        $uploadedFiles = $request->file('documentFiles', []);
        if (!is_array($uploadedFiles) || empty($uploadedFiles)) {
            return back()->withErrors([
                'documentFiles' => 'Vui long chon it nhat 1 tep de upload.',
            ]);
        }

        $uploadedCount = 0;
        $failedUploads = [];

        foreach ($uploadedFiles as $uploadedFile) {
            if (!$uploadedFile || !$uploadedFile->isValid()) {
                $failedUploads[] = 'Mot tep khong hop le hoac da bi loi khi tai len.';
                continue;
            }

            $baseName = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = strtolower((string) $uploadedFile->getClientOriginalExtension());
            $safeBaseName = Str::slug($baseName);
            if ($safeBaseName === '') {
                $safeBaseName = 'tai-lieu';
            }

            $objectPath = sprintf(
                '%s/%s_%s_%s.%s',
                $folder['key'],
                now()->format('Ymd_His_u'),
                $safeBaseName,
                Str::lower(Str::random(6)),
                $extension
            );

            $fileContent = file_get_contents($uploadedFile->getRealPath());
            if ($fileContent === false) {
                $failedUploads[] = 'Khong the doc tep ' . $uploadedFile->getClientOriginalName() . '.';
                continue;
            }

            try {
                $this->uploadObject(
                    $objectPath,
                    $fileContent,
                    $uploadedFile->getMimeType() ?: 'application/octet-stream',
                    false
                );

                $uploadedCount++;
            } catch (\Throwable $exception) {
                Log::error('Upload tai lieu len Supabase that bai', [
                    'error' => $exception->getMessage(),
                    'file' => $uploadedFile->getClientOriginalName(),
                ]);
                $failedUploads[] = $uploadedFile->getClientOriginalName() . ' (' . $exception->getMessage() . ')';
            }
        }

        if ($uploadedCount === 0) {
            return back()->withErrors([
                'documentFiles' => 'Khong the upload tep nao. Chi tiet: ' . implode('; ', $failedUploads),
            ]);
        }

        $successMessage = $uploadedCount === 1
            ? 'Upload 1 tep len Supabase thanh cong.'
            : 'Upload ' . $uploadedCount . ' tep len Supabase thanh cong.';

        $redirect = redirect()->route('admin.dashboard', ['folder' => $folder['key']])
            ->with('success', $successMessage);

        if (!empty($failedUploads)) {
            $redirect->with('warning', 'Mot so tep upload that bai: ' . implode('; ', $failedUploads));
        }

        return $redirect;
    }

    public function deleteFolderFile(Request $request, string $folderKey)
    {
        $validated = $request->validate([
            'file_name' => ['required', 'string', 'max:255'],
        ]);

        $folders = $this->loadFolders();
        $folder = collect($folders)->firstWhere('key', $folderKey);

        if ($folder === null) {
            return back()->withErrors([
                'file_name' => 'Folder khong ton tai.',
            ]);
        }

        $fileName = basename((string) $validated['file_name']);
        $objectPath = $folderKey . '/' . $fileName;

        try {
            $this->deleteObject($objectPath);

            return redirect()->route('admin.dashboard', ['folder' => $folderKey])
                ->with('success', 'Da xoa tep khoi folder.');
        } catch (\Throwable $exception) {
            Log::error('Xoa tep Supabase that bai', [
                'error' => $exception->getMessage(),
                'path' => $objectPath,
            ]);

            return back()->withErrors([
                'file_name' => 'Khong the xoa tep: ' . $exception->getMessage(),
            ]);
        }
    }

    private function loadFolders(): array
    {
        $remoteFolderKeys = $this->listRootFolders();
        $metadataMap = $this->loadFolderMetadataMap();

        $folders = [];
        foreach ($remoteFolderKeys as $folderKey) {
            $meta = $metadataMap[$folderKey] ?? null;

            $folders[] = [
                'key' => $folderKey,
                'name' => (string) ($meta['name'] ?? $folderKey),
                'created_at' => (string) ($meta['created_at'] ?? ''),
            ];
        }

        usort($folders, function (array $left, array $right) {
            return strcmp((string) ($left['name'] ?? ''), (string) ($right['name'] ?? ''));
        });

        return $folders;
    }

    private function loadFolderMetadataMap(): array
    {
        if (!Storage::disk('local')->exists(self::FOLDERS_META_FILE)) {
            return [];
        }

        $raw = Storage::disk('local')->get(self::FOLDERS_META_FILE);
        $decoded = json_decode($raw, true);

        if (!is_array($decoded)) {
            return [];
        }

        $metadataMap = [];
        foreach ($decoded as $row) {
            $key = trim((string) ($row['key'] ?? ''));
            $name = trim((string) ($row['name'] ?? ''));

            if ($key === '') {
                continue;
            }

            if ($name === '') {
                $name = $key;
            }

            $metadataMap[$key] = [
                'name' => $name,
                'created_at' => (string) ($row['created_at'] ?? ''),
            ];
        }

        return $metadataMap;
    }

    private function saveFolders(array $folders): void
    {
        $normalized = [];

        foreach ($folders as $row) {
            $key = trim((string) ($row['key'] ?? ''));
            if ($key === '') {
                continue;
            }

            $name = trim((string) ($row['name'] ?? ''));
            if ($name === '') {
                $name = $key;
            }

            $normalized[] = [
                'key' => $key,
                'name' => $name,
                'created_at' => (string) ($row['created_at'] ?? ''),
            ];
        }

        Storage::disk('local')->put(
            self::FOLDERS_META_FILE,
            json_encode(array_values($normalized), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
        );
    }

    private function buildFolderKey(string $folderName): string
    {
        $baseKey = Str::slug($folderName);
        if ($baseKey === '') {
            $baseKey = 'nam-hoc';
        }

        return $baseKey;
    }

    private function listRootFolders(): array
    {
        $config = $this->getSupabaseConfig();

        /** @var Response $response */
        $response = Http::withHeaders([
            'apikey' => $config['service_role_key'],
            'Authorization' => 'Bearer ' . $config['service_role_key'],
            'Content-Type' => 'application/json',
        ])
            ->timeout(60)
            ->post($config['url'] . '/storage/v1/object/list/' . $config['bucket'], [
                'prefix' => '',
                'limit' => 1000,
                'offset' => 0,
                'sortBy' => [
                    'column' => 'name',
                    'order' => 'asc',
                ],
            ]);

        if (! $response->successful()) {
            throw new RuntimeException($this->formatSupabaseError($response));
        }

        $payload = $response->json();
        $items = is_array($payload) ? $payload : [];

        $folders = [];

        foreach ($items as $item) {
            $name = trim((string) ($item['name'] ?? ''));

            if ($name === '' || $name === '.keep') {
                continue;
            }

            $isFolder = $this->isFolderListItem($item);

            if ($isFolder) {
                $folders[] = $name;
                continue;
            }

            if (str_contains($name, '/')) {
                $rootFolder = trim((string) Str::before($name, '/'));
                if ($rootFolder !== '') {
                    $folders[] = $rootFolder;
                }
            }
        }

        $folders = array_values(array_unique($folders));
        sort($folders);

        return $folders;
    }

    private function getSupabaseConfig(): array
    {
        $url = rtrim((string) config('services.supabase.url'), '/');
        $serviceRoleKey = trim((string) config('services.supabase.service_role_key'));
        $bucket = trim((string) config('services.supabase.storage_bucket'));

        if ($url === '' || $serviceRoleKey === '' || $bucket === '') {
            throw new RuntimeException('Thieu cau hinh SUPABASE_URL, SUPABASE_SERVICE_ROLE_KEY hoac SUPABASE_STORAGE_BUCKET.');
        }

        return [
            'url' => $url,
            'service_role_key' => $serviceRoleKey,
            'bucket' => $bucket,
        ];
    }

    private function uploadObject(string $objectPath, string $content, string $contentType, bool $upsert): void
    {
        $config = $this->getSupabaseConfig();

        /** @var Response $response */
        $response = Http::withHeaders([
            'apikey' => $config['service_role_key'],
            'Authorization' => 'Bearer ' . $config['service_role_key'],
            'x-upsert' => $upsert ? 'true' : 'false',
        ])
            ->withBody($content, $contentType)
            ->timeout(120)
            ->post(
                $config['url'] . '/storage/v1/object/' . $config['bucket'] . '/' . $this->encodeObjectPath($objectPath)
            );

        if (! $response->successful()) {
            throw new RuntimeException($this->formatSupabaseError($response));
        }
    }

    private function listFolderFiles(string $folderKey): array
    {
        $items = $this->listObjectsByPrefix($folderKey, 200);

        $files = [];
        foreach ($items as $item) {
            if ($this->isFolderListItem($item)) {
                continue;
            }

            $name = (string) ($item['name'] ?? '');
            if ($name === '' || $name === '.keep') {
                continue;
            }

            $files[] = [
                'name' => $name,
                'size' => (int) ($item['metadata']['size'] ?? 0),
                'updated_at' => (string) ($item['updated_at'] ?? $item['created_at'] ?? '-'),
            ];
        }

        return $files;
    }

    private function renameFolderOnSupabase(string $sourceFolderKey, string $destinationFolderKey): void
    {
        $sourceFolderKey = trim($sourceFolderKey);
        $destinationFolderKey = trim($destinationFolderKey);

        if ($sourceFolderKey === '' || $destinationFolderKey === '') {
            throw new RuntimeException('Folder key khong hop le de doi ten.');
        }

        if ($sourceFolderKey === $destinationFolderKey) {
            return;
        }

        $objects = $this->listFolderObjectPaths($sourceFolderKey);

        foreach ($objects as $sourcePath) {
            $relativePath = Str::after($sourcePath, $sourceFolderKey . '/');
            if ($relativePath === '') {
                continue;
            }

            $destinationPath = $destinationFolderKey . '/' . $relativePath;
            $this->moveObject($sourcePath, $destinationPath);
        }

        // Ensure destination folder exists even when source has no files.
        $this->uploadObject($destinationFolderKey . '/.keep', '', 'text/plain', true);

        try {
            $this->deleteObject($sourceFolderKey . '/.keep');
        } catch (\Throwable $exception) {
            // Ignore cleanup failures for legacy folders that may not contain a marker file.
        }
    }

    private function listFolderObjectPaths(string $folderKey): array
    {
        $items = $this->listObjectsByPrefix($folderKey, 1000);
        $paths = [];

        foreach ($items as $item) {
            if ($this->isFolderListItem($item)) {
                continue;
            }

            $name = trim((string) ($item['name'] ?? ''));
            if ($name === '') {
                continue;
            }

            $paths[] = $folderKey . '/' . ltrim($name, '/');
        }

        return array_values(array_unique($paths));
    }

    private function moveObject(string $sourcePath, string $destinationPath): void
    {
        $config = $this->getSupabaseConfig();

        /** @var Response $response */
        $response = Http::withHeaders([
            'apikey' => $config['service_role_key'],
            'Authorization' => 'Bearer ' . $config['service_role_key'],
            'Content-Type' => 'application/json',
        ])
            ->timeout(120)
            ->post($config['url'] . '/storage/v1/object/move', [
                'bucketId' => $config['bucket'],
                'sourceKey' => ltrim($sourcePath, '/'),
                'destinationKey' => ltrim($destinationPath, '/'),
            ]);

        if (! $response->successful()) {
            throw new RuntimeException($this->formatSupabaseError($response));
        }
    }

    private function listObjectsByPrefix(string $prefix, int $limit = 200): array
    {
        $config = $this->getSupabaseConfig();
        $allItems = [];
        $offset = 0;

        do {
            /** @var Response $response */
            $response = Http::withHeaders([
                'apikey' => $config['service_role_key'],
                'Authorization' => 'Bearer ' . $config['service_role_key'],
                'Content-Type' => 'application/json',
            ])
                ->timeout(60)
                ->post($config['url'] . '/storage/v1/object/list/' . $config['bucket'], [
                    'prefix' => $prefix,
                    'limit' => $limit,
                    'offset' => $offset,
                    'sortBy' => [
                        'column' => 'name',
                        'order' => 'asc',
                    ],
                ]);

            if (! $response->successful()) {
                throw new RuntimeException($this->formatSupabaseError($response));
            }

            $payload = $response->json();
            $items = is_array($payload) ? $payload : [];
            $allItems = array_merge($allItems, $items);
            $offset += $limit;
        } while (count($items) === $limit);

        return $allItems;
    }

    private function isFolderListItem(array $item): bool
    {
        return (array_key_exists('id', $item) && (($item['id'] ?? null) === null || (string) $item['id'] === ''))
            || (($item['metadata'] ?? null) === null);
    }

    private function deleteObject(string $objectPath): void
    {
        $config = $this->getSupabaseConfig();

        /** @var Response $response */
        $response = Http::withHeaders([
            'apikey' => $config['service_role_key'],
            'Authorization' => 'Bearer ' . $config['service_role_key'],
        ])
            ->timeout(60)
            ->delete(
                $config['url'] . '/storage/v1/object/' . $config['bucket'] . '/' . $this->encodeObjectPath($objectPath)
            );

        if (! $response->successful()) {
            throw new RuntimeException($this->formatSupabaseError($response));
        }
    }

    private function encodeObjectPath(string $path): string
    {
        $segments = array_map('rawurlencode', explode('/', ltrim($path, '/')));

        return implode('/', $segments);
    }

    private function formatSupabaseError($response): string
    {
        $message = $response->json('message')
            ?? $response->json('error')
            ?? $response->json('details')
            ?? ('HTTP ' . $response->status());

        if (is_array($message)) {
            $message = json_encode($message, JSON_UNESCAPED_UNICODE);
        }

        return (string) $message;
    }
}
