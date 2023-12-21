<?php

namespace App\Models;

use App\Enums\TaskStatus;
use App\Models\TaskDocument;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'description', 'status', 'user_id'];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'status' => TaskStatus::class,
        'created_at' => 'date:m/d/Y H:i:s',
    ];

    public function attachments(): HasMany
    {
        return $this->hasMany(TaskDocument::class);
    }
}
