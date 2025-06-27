<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('course_contents', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200);      
            $table->text('description')->nullable();
            // $table->text('description');
            $table->string('video_url', 200)->nullable(); 
            $table->string('file_attachment')->nullable(); 
            $table->foreignId('course_id')->constrained('courses')->onDelete('restrict'); 
            $table->foreignId('parent_id')->nullable()->constrained('course_contents')->onDelete('restrict'); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_contents');
    }
};
