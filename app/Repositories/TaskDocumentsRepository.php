<?php

namespace App\Repositories;

use App\Models\TaskDocument;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class TaskDocumentsRepository
{
    public function insertTaskDocuments($documents)
    {
        $isSave = TaskDocument::insert($documents);

        if (!$isSave) {
            throw new \Exception("Something wrong, unable to save this records!", HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $isSave;
    }

    public function findTaskDocuments($taskID)
    {
        $documents = TaskDocument::where('task_id', $taskID)->get();

        if (!$documents) {
            throw new \Exception("Task {$taskID} is not found!", HttpResponse::HTTP_NOT_FOUND);
        }

        return $documents;
    }
}
