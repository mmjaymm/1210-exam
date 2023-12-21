<?php

namespace App\Repositories;

use App\Models\Task;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class TaskRepository
{
    public function insertTask($task)
    {
        $isSave = Task::create($task);

        if (!$isSave) {
            throw new \Exception("Something wrong, unable to save this records!", HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $isSave;
    }

    public function updateTask($task, $id)
    {
        try {
            $isUpdated = Task::where([
                'id' => $id
            ])->update($task);

            if (!$isUpdated) {
                throw new \Exception("Something wrong, unable to update this record!", HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
            }

            return $isUpdated;
        } catch (\Exception $ex) {
            Log::channel('daily')->critical(
                $ex->getMessage(),
                [
                    'ClassName : ' . get_class($this),
                    'Method Name : ' . __FUNCTION__,
                    'Line : ' . __LINE__
                ]
            );
            throw new \Exception($ex->getMessage(), $ex->getCode());
        }
    }

    public function destroyTask($id)
    {
        try {
            $task = $this->findTask($id);

            $isDeleted = $task->delete();

            if (!$isDeleted) {
                throw new \Exception("Something wrong, unable to delete this record!", HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
            }

            return $isDeleted;
        } catch (\Exception $ex) {
            Log::channel('daily')->critical(
                $ex->getMessage(),
                [
                    'ClassName : ' . get_class($this),
                    'Method Name : ' . __FUNCTION__,
                    'Line : ' . __LINE__
                ]
            );
            throw new \Exception($ex->getMessage(), $ex->getCode());
        }
    }

    public function findTask($id)
    {
        $task = Task::find($id);

        if (!$task) {
            throw new \Exception("Task {$id} is not found!", HttpResponse::HTTP_NOT_FOUND);
        }

        return $task;
    }

    public function allTasks($perPage = 10)
    {
        $tasks = Task::latest()->paginate($perPage);

        if (!$tasks) {
            throw new \Exception("No Records Found!", HttpResponse::HTTP_NOT_FOUND);
        }

        return $tasks;
    }

    public function findTaskWithDocuments($taskID)
    {
        $task = $this->findTask($taskID);

        return $task;
    }
}
