<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gso_signed_document_archives', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('document_type', 20);
            $table->string('document_number', 255);
            $table->string('drive_file_id', 120);
            $table->string('drive_folder_id', 120)->nullable();
            $table->string('file_name', 255)->nullable();
            $table->string('folder_path', 255)->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();

            $table->unique(['document_type', 'document_number'], 'gso_signed_document_archives_doc_unique');
            $table->index('drive_file_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gso_signed_document_archives');
    }
};
