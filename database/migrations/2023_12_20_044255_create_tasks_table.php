<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\TaskStatus;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {

            $taskStatus = array_column(TaskStatus::cases(), 'value');

            $table->id();
            $table->string('name');
            $table->text('description');
            $table->enum('status', $taskStatus)->default(TaskStatus::Todo->value);
            $table->softDeletes();
            $table->timestamps();
            $table->foreignId('user_id')->nullable()->constrained();
            $table->foreignId('task_id')->nullable()->constrained();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
