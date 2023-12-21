<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Services\TaskService;
use App\Services\TaskServices;
use Illuminate\Support\Carbon;
use App\Repositories\TaskRepository;
use Inertia\Response as InertiaResponse;
use App\Repositories\TaskDocumentsRepository;
use Symfony\Component\HttpFoundation\Response as  HttpResponse;

class TaskController extends Controller
{
    private $taskSrvc;
    private $taskRepo;

    public function __construct(TaskService $taskService, TaskRepository $taskRepository)
    {
        $this->taskSrvc = $taskService;
        $this->taskRepo = $taskRepository;
    }

    public function index(): InertiaResponse
    {
        return Inertia::render('Tasks');
    }

    public function create(Request $request)
    {
        try {
            $task = $this->taskSrvc->insertTask($request);
            return $this->successResponse($task, 'Task Inserted!', HttpResponse::HTTP_CREATED);
        } catch (\Exception $ex) {
            return $this->errorResponse($ex->getCode(), $ex->getMessage());
        }
    }

    public function getAllTasks(Request $request)
    {

        try {
            $pageSize = $request->input('page_size');
            $searchTerm = $request->input('search');
            $tasks = $this->taskRepo->allTasks($pageSize, $searchTerm);
            return $this->successResponse($tasks, 'Task Retrieved!', HttpResponse::HTTP_OK);
        } catch (\Exception $ex) {
            return $this->errorResponse($ex->getCode(), $ex->getMessage());
        }
    }

    public function destroy(int $id)
    {
        try {
            $isDestroy = $this->taskRepo->destroyTask($id);
            return $this->successResponse($isDestroy, "Task Deleted!", HttpResponse::HTTP_OK);
        } catch (\Exception $ex) {
            return $this->errorResponse($ex->getCode(), $ex->getMessage());
        }
    }

    public function show(int $id)
    {
        try {
            $task = $this->taskRepo->findTask($id);
            return $this->successResponse($task, "Task Retrieved!", HttpResponse::HTTP_OK);
        } catch (\Exception $ex) {
            return $this->errorResponse($ex->getCode(), $ex->getMessage());
        }
    }

    public function update(Request $request, int $id)
    {
        try {
            $task = $this->taskSrvc->updateTask($request, $id);
            return $this->successResponse($task, 'Task Updated!', HttpResponse::HTTP_OK);
        } catch (\Exception $ex) {
            return $this->errorResponse($ex->getCode(), $ex->getMessage());
        }
    }
}
