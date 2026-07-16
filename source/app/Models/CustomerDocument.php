<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class CustomerDocument extends Model
{
    protected $fillable = [
        'customer_id',
        'service_type',
        'document_type',
        'document_types',
        'title',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
        'documents',
        'expiry_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'document_types' => 'array',
        'documents' => 'array',
        'expiry_date' => 'date',
    ];

    protected static function booted(): void
    {
        static::deleting(function (CustomerDocument $customerDocument): void {
            foreach ($customerDocument->documents_list as $document) {
                $filePath = $document['file_path'] ?? null;

                if ($filePath && Storage::disk('public')->exists($filePath)) {
                    Storage::disk('public')->delete($filePath);
                }
            }
        });
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function getDocumentsListAttribute(): array
    {
        $documents = $this->documents;

        if (is_array($documents) && count($documents) > 0) {
            return $documents;
        }

        if ($this->file_path) {
            return [
                [
                    'file_name' => $this->file_name ?: basename($this->file_path),
                    'file_path' => $this->file_path,
                    'file_type' => $this->file_type ?: strtoupper(pathinfo($this->file_path, PATHINFO_EXTENSION)),
                    'file_size' => (int) $this->file_size,
                ],
            ];
        }

        return [];
    }

    public function getDocumentsCountAttribute(): int
    {
        return count($this->documents_list);
    }

    public function getDocumentTypesTextAttribute(): string
    {
        $types = $this->document_types;

        if (is_array($types) && count($types) > 0) {
            return implode(', ', $types);
        }

        return $this->document_type ?: '-';
    }

    public function getFormattedFileSizeAttribute(): string
    {
        $bytes = 0;

        foreach ($this->documents_list as $document) {
            $bytes += (int) ($document['file_size'] ?? 0);
        }

        if ($bytes <= 0) {
            $bytes = (int) $this->file_size;
        }

        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        }

        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }

        return $bytes . ' Bytes';
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }
}