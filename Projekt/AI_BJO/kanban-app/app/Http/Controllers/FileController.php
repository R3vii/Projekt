<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileController extends Controller
{
    public function store(Request $request, Task $task)
    {
        $user = Auth::user();

        // Authorization
        if (!$user->isLeaderOrAdmin() && !$task->isAssigned($user)) {
            return back()->with('error', 'Nie masz uprawnień do dodawania plików do tego zadania.');
        }

        $request->validate([
            'file' => ['required', 'file', 'max:20480'], // 20MB max
        ], [
            'file.required' => 'Wybierz plik do przesłania.',
            'file.max' => 'Plik nie może przekraczać 20MB.',
        ]);

        $uploadedFile = $request->file('file');
        $storedName = Str::uuid() . '.' . $uploadedFile->getClientOriginalExtension();
        $path = $uploadedFile->storeAs('task-files', $storedName, 'local');

        TaskFile::create([
            'task_id' => $task->id,
            'uploaded_by' => Auth::id(),
            'original_name' => $uploadedFile->getClientOriginalName(),
            'stored_name' => $storedName,
            'path' => $path,
            'mime_type' => $uploadedFile->getMimeType(),
            'size' => $uploadedFile->getSize(),
            'status' => 'pending',
        ]);

        return back()->with('success', "Plik \"{$uploadedFile->getClientOriginalName()}\" został przesłany i oczekuje na zatwierdzenie.");
    }

    public function approve(Request $request, TaskFile $file)
    {
        $user = Auth::user();

        if (!$user->isLeaderOrAdmin()) {
            return back()->with('error', 'Brak uprawnień.');
        }

        // Leader can only approve files for their category
        if ($user->isLeader() && !$user->canManageCategory($file->task->getStatusCategory())) {
            return back()->with('error', 'Brak uprawnień do zatwierdzenia plików w tej kategorii.');
        }

        $file->update([
            'status' => 'approved',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'feedback' => null,
        ]);

        return back()->with('success', "Plik \"{$file->original_name}\" został zatwierdzony.");
    }

    public function reject(Request $request, TaskFile $file)
    {
        $user = Auth::user();

        if (!$user->isLeaderOrAdmin()) {
            return back()->with('error', 'Brak uprawnień.');
        }

        $request->validate([
            'feedback' => ['nullable', 'string', 'max:500'],
        ]);

        $file->update([
            'status' => 'rejected',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'feedback' => $request->feedback,
        ]);

        return back()->with('success', "Plik \"{$file->original_name}\" został odrzucony.");
    }

    public function download(TaskFile $file)
    {
        $user = Auth::user();

        // Check access: admin, leader of category, or assigned user
        $task = $file->task;
        if (!$user->isAdmin()
            && !($user->isLeader() && $user->canManageCategory($task->getStatusCategory()))
            && !$task->isAssigned($user)) {
            return back()->with('error', 'Brak uprawnień do pobrania tego pliku.');
        }

        if (!Storage::disk('local')->exists($file->path)) {
            abort(404, 'Plik nie istnieje.');
        }

        return Storage::disk('local')->download($file->path, $file->original_name);
    }

    public function destroy(TaskFile $file)
    {
        $user = Auth::user();
        $task = $file->task;

        // Check access: admin, leader of category, or the uploader
        if (!$user->isAdmin()
            && !($user->isLeader() && $user->canManageCategory($task->getStatusCategory()))
            && $file->uploaded_by !== $user->id) {
            return back()->with('error', 'Nie masz uprawnień do usunięcia tego pliku.');
        }

        if (Storage::disk('local')->exists($file->path)) {
            Storage::disk('local')->delete($file->path);
        }

        $fileName = $file->original_name;
        $file->delete();

        return back()->with('success', "Plik \"{$fileName}\" został usunięty.");
    }
}
