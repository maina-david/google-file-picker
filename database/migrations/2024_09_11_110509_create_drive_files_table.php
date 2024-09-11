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
        Schema::create('drive_files', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('file_id');
            $table->string('mime_type');
            $table->string('url');
            $table->string('icon_url')->nullable();
            $table->string('embed_url')->nullable();
            $table->string('organization_display_name')->nullable();
            $table->boolean('is_shared')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drive_files');
    }
};