<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IdScan extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_REVIEW = 'review';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    protected $table = 'id_scans';

    protected $fillable = [
        'userId',
        'documentType',
        'idNumberHash',
        'fullName',
        'idNumber',
        'dateOfBirth',
        'expirationDate',
        'address',
        'extractedData',
        'rawOcrText',
        'frontImagePath',
        'backImagePath',
        'frontImageMime',
        'backImageMime',
        'ocrConfidence',
        'qualityScore',
        'blurScore',
        'glareScore',
        'status',
        'reviewNotes',
        'rejectionReason',
        'reviewedBy',
        'reviewedAt',
    ];

    protected function casts(): array
    {
        return [
            'fullName' => 'encrypted',
            'idNumber' => 'encrypted',
            'address' => 'encrypted',
            'rawOcrText' => 'encrypted',
            'extractedData' => 'encrypted:array',
            'ocrConfidence' => 'decimal:2',
            'qualityScore' => 'decimal:2',
            'blurScore' => 'decimal:2',
            'glareScore' => 'decimal:2',
            'reviewedAt' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'userId');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewedBy');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function getDocumentTypeLabelAttribute(): string
    {
        return match ($this->documentType) {
            'national_id' => 'National ID (PhilSys)',
            'drivers_license' => "Driver's License",
            'passport' => 'Passport',
            'umid' => 'UMID',
            'philhealth_id' => 'PhilHealth ID',
            'student_id' => 'Student ID',
            'voters_id' => "Voter's ID",
            default => 'Other Government ID',
        };
    }
}
