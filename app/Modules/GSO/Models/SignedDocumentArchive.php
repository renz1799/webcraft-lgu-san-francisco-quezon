<?php

namespace App\Modules\GSO\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;

class SignedDocumentArchive extends Model
{
    use HasUuid;

    protected $table = 'gso_signed_document_archives';

    protected $fillable = [
        'document_type',
        'document_number',
        'drive_file_id',
        'drive_folder_id',
        'file_name',
        'folder_path',
        'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'archived_at' => 'datetime',
        ];
    }
}
