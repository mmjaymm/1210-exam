<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Repositories\TaskRepository;
use Illuminate\Support\Facades\Storage;
use App\Repositories\TaskDocumentsRepository;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class TaskService
{
    protected $taskRepo;
    protected $taskDocuRepo;
    /**
     *
     */
    public function __construct(TaskRepository $taskRepository, TaskDocumentsRepository $taskDocuRepository)
    {
        $this->taskRepo = $taskRepository;
        $this->taskDocuRepo = $taskDocuRepository;
    }

    public function uploadAttachment($document, $folder)
    {
        $timestamp = Carbon::parse(now())->timestamp; //UTC
        $hash = (string) Str::uuid();
        $filename = $hash . '-' . $timestamp . '.' . $document->getClientOriginalExtension();
        $pathfile = $document->storeAs($folder, $filename);
        return $pathfile;
    }

    public function uploadTaskAttachments($request)
    {
        if ($request->hasFile('documents')) {
            $documents = [];

            foreach ($request->file('documents') as $key => $attachment) {
                $pathfile = $this->uploadAttachment($attachment, 'task-documents');
                if (!$pathfile) {
                    return false;
                }

                $documents[$key]['url'] = $pathfile;
            }
            return 'with-attachment';
        }
        return 'no-attachment';
    }

    public function insertTask($request)
    {
        try {
            $documents = $this->uploadTaskAttachments($request);

            if (!$documents) {
                throw new \Exception("Unable to upload!", HttpResponse::HTTP_UNPROCESSABLE_ENTITY);
            }

            $taskData = $request->all();

            $task = $this->taskRepo->insertTask($taskData);

            if ($documents == 'with-attachment') {
                $taskID = $task->id;
                $documents = collect($documents)->map(function ($document) use ($taskID) {
                    $document['task_id'] = $taskID;
                    return $document;
                });
                $this->taskDocuRepo->insertTaskDocuments($documents->toArray());
            }

            return true;
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage(), HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function updateTask($request, $taskID)
    {
        try {
            $documents = $this->uploadTaskAttachments($request);

            if (!$documents) {
                throw new \Exception("Unable to upload!", HttpResponse::HTTP_UNPROCESSABLE_ENTITY);
            }

            if ($documents == 'with-attachment') {
                //delete old attachment
                $task = $this->taskRepo->findTaskWithDocuments($taskID);
                if ($task->attachments->count() > 0) {
                    $task->attachments()->delete();
                    $attachmentUrls = implode(', ', $task->attachments->pluck('url')->all());
                    Storage::delete($attachmentUrls);
                }

                //insert new attachment
                $documents = collect($documents)->map(function ($document) use ($taskID) {
                    $document['task_id'] = $taskID;
                    return $document;
                });
                $this->taskDocuRepo->insertTaskDocuments($documents->toArray());
            }

            $taskData = $request->all();
            $this->taskRepo->updateTask($taskData, $taskID);

            return true;
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage(), HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
