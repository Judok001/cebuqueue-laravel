<?php

namespace App\Models;

use Database\Factories\QueueTicketFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'public_token',
    'patient_id',
    'service_id',
    'handled_by',
    'queue_date',
    'sequence',
    'ticket_code',
    'status',
    'source',
    'priority',
    'called_at',
    'served_at',
])]
class QueueTicket extends Model
{
    public const STATUS_WAITING = 'waiting';

    public const STATUS_SERVING = 'serving';

    public const STATUS_SERVED = 'served';

    public const STATUS_ABSENT = 'absent';

    public const PRIORITY_EMERGENCY = 'emergency';

    public const PRIORITY_SENIOR = 'senior';

    public const PRIORITY_PWD = 'pwd';

    public const PRIORITY_PREGNANT = 'pregnant';

    public const PRIORITY_REGULAR = 'regular';

    /**
     * @var array<string, string>
     */
    public const PRIORITIES = [
        self::PRIORITY_EMERGENCY => 'Emergency',
        self::PRIORITY_SENIOR => 'Senior citizen',
        self::PRIORITY_PWD => 'Person with disability',
        self::PRIORITY_PREGNANT => 'Pregnant',
        self::PRIORITY_REGULAR => 'Regular',
    ];

    public const SOURCE_ONLINE = 'online';

    public const SOURCE_WALK_IN = 'walk_in';

    /** @use HasFactory<QueueTicketFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'queue_date' => 'date',
            'sequence' => 'integer',
            'called_at' => 'datetime',
            'served_at' => 'datetime',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    public function priorityLabel(): string
    {
        return self::PRIORITIES[$this->priority] ?? self::PRIORITIES[self::PRIORITY_REGULAR];
    }

    public function priorityRank(): int
    {
        return match ($this->priority) {
            self::PRIORITY_EMERGENCY => 0,
            self::PRIORITY_SENIOR, self::PRIORITY_PWD, self::PRIORITY_PREGNANT => 1,
            default => 2,
        };
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    #[Scope]
    protected function inPriorityOrder(Builder $query): Builder
    {
        return $query
            ->orderByRaw("CASE WHEN priority = 'emergency' THEN 0 WHEN priority IN ('senior', 'pwd', 'pregnant') THEN 1 ELSE 2 END")
            ->orderBy('created_at')
            ->orderBy('id');
    }

    public function queuePosition(): ?int
    {
        if ($this->status !== self::STATUS_WAITING) {
            return null;
        }

        $ticketsAhead = self::query()
            ->where('service_id', $this->service_id)
            ->where('queue_date', $this->queue_date)
            ->whereIn('status', [self::STATUS_WAITING, self::STATUS_SERVING])
            ->where(function (Builder $query): void {
                $query->where('status', self::STATUS_SERVING)
                    ->orWhere(function (Builder $query): void {
                        $query->whereRaw(
                            "CASE WHEN priority = 'emergency' THEN 0 WHEN priority IN ('senior', 'pwd', 'pregnant') THEN 1 ELSE 2 END < ?",
                            [$this->priorityRank()],
                        )->orWhere(function (Builder $query): void {
                            $query->whereRaw(
                                "CASE WHEN priority = 'emergency' THEN 0 WHEN priority IN ('senior', 'pwd', 'pregnant') THEN 1 ELSE 2 END = ?",
                                [$this->priorityRank()],
                            )->where(function (Builder $query): void {
                                $query->where('created_at', '<', $this->created_at)
                                    ->orWhere(function (Builder $query): void {
                                        $query->where('created_at', $this->created_at)
                                            ->where('id', '<', $this->id);
                                    });
                            });
                        });
                    });
            })
            ->count();

        return $ticketsAhead + 1;
    }

    public function estimatedWaitMinutes(): ?int
    {
        $position = $this->queuePosition();

        if ($position === null) {
            return null;
        }

        return ($position - 1) * $this->service->estimated_minutes;
    }
}
