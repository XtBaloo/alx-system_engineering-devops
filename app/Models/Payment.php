<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use App\Traits\Auditable;

class Payment extends Model
{
    use Auditable, HasFactory;

    protected $fillable = [
        'student_fee_id', 'receipt_number', 'amount', 'payment_method',
        'payment_date', 'received_by', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'payment_date' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $payment) {
            if (empty($payment->receipt_number)) {
                $payment->receipt_number = static::generateReceiptNumber();
            }
        });
    }

    public static function generateReceiptNumber(): string
    {
        do {
            $number = 'PFA-'.now()->format('Ymd').'-'.strtoupper(Str::random(5));
        } while (static::where('receipt_number', $number)->exists());

        return $number;
    }

    public function studentFee(): BelongsTo
    {
        return $this->belongsTo(StudentFee::class);
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}
