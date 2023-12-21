<?php

namespace App\Models;

use App\Models\Task;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TaskDocument extends Model
{
    use HasFactory;

    protected $fillable = ['url', 'description', 'task_id'];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($taskDocument) {
            if (Storage::exists($taskDocument->url)) {
                Storage::delete($taskDocument->url);
            }
        });
    }

    public function task()
    {
        return $this->belongsTo(Task::class, 'task_id', 'id');
    }
}
