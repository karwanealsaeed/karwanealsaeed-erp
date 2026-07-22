<?php

namespace App\Models;

use Illuminate\Validation\ValidationException;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UmrahGroupFlight extends Model
{
    protected $fillable = [
        'group_code',
        'title',
        'airline_id',
        'airline',
        'airline_code',
        'route',
        'from_city',
        'to_city',
        'return_city',
        'departure_date',
        'return_date',
        'duration_days',
        'duration_nights',
        'total_flights',
        'seats_total',
        'seats_left',
        'baggage',
        'meals',
        'flight_segments',
        'status',
        'notes',
    ];

    protected $casts = [
        'departure_date' => 'date',
        'return_date' => 'date',
        'flight_segments' => 'array',
    ];

    protected static function booted(): void
    {
        static::saving(function (UmrahGroupFlight $record): void {
            if (filled($record->airline_id)) {
                $airline = Airline::query()
                    ->where('id', $record->airline_id)
                    ->where('status', true)
                    ->first();

                if (! $airline) {
                    throw ValidationException::withMessages([
                        'airline_id' => 'Please select an active airline.',
                    ]);
                }

                $record->airline = $airline->name;
                $record->airline_code = $airline->code;
            }
        });


        static::creating(function (UmrahGroupFlight $groupFlight): void {
            if (blank($groupFlight->group_code)) {
                $nextId = ((int) self::max('id')) + 1;
                $groupFlight->group_code = 'UG-' . str_pad((string) $nextId, 5, '0', STR_PAD_LEFT);
            }
        });

        static::saving(function (UmrahGroupFlight $groupFlight): void {
            $segments = is_array($groupFlight->flight_segments) ? array_values($groupFlight->flight_segments) : [];

            if (count($segments) > 0) {
                $firstSegment = $segments[0] ?? [];
                $lastSegment = $segments[count($segments) - 1] ?? [];

                if (blank($groupFlight->from_city) && filled($firstSegment['from'] ?? null)) {
                    $groupFlight->from_city = strtoupper((string) $firstSegment['from']);
                }

                if (blank($groupFlight->to_city) && filled($firstSegment['to'] ?? null)) {
                    $groupFlight->to_city = strtoupper((string) $firstSegment['to']);
                }

                if (blank($groupFlight->return_city) && filled($lastSegment['to'] ?? null)) {
                    $groupFlight->return_city = strtoupper((string) $lastSegment['to']);
                }

                if (blank($groupFlight->route) && $groupFlight->from_city && $groupFlight->to_city && $groupFlight->return_city) {
                    $groupFlight->route = strtoupper($groupFlight->from_city . '-' . $groupFlight->to_city . '-' . $groupFlight->return_city);
                }

                if (blank($groupFlight->departure_date) && filled($firstSegment['departure_date'] ?? null)) {
                    $groupFlight->departure_date = $firstSegment['departure_date'];
                }

                if (blank($groupFlight->return_date)) {
                    if (filled($lastSegment['departure_date'] ?? null)) {
                        $groupFlight->return_date = $lastSegment['departure_date'];
                    } elseif (filled($lastSegment['arrival_date'] ?? null)) {
                        $groupFlight->return_date = $lastSegment['arrival_date'];
                    }
                }

                $groupFlight->total_flights = count($segments);
            }

            if ($groupFlight->departure_date && $groupFlight->return_date) {
                $departure = Carbon::parse($groupFlight->departure_date)->startOfDay();
                $return = Carbon::parse($groupFlight->return_date)->startOfDay();

                $groupFlight->duration_nights = max($departure->diffInDays($return), 0);
                $groupFlight->duration_days = $groupFlight->duration_nights;
            }

            if ((int) $groupFlight->seats_left <= 0 && (int) $groupFlight->seats_total > 0 && ! $groupFlight->exists) {
                $groupFlight->seats_left = $groupFlight->seats_total;
            }
        });
    }

    public function airlineMaster(): BelongsTo
    {
        return $this->belongsTo(
            Airline::class,
            'airline_id',
        );
    }
    public function packages(): HasMany
    {
        return $this->hasMany(UmrahPackage::class);
    }

    public function getDisplayTitleAttribute(): string
    {
        return trim($this->group_code . ' - ' . ($this->route ?: $this->title));
    }

    public function reduceSeats(int $seats): void
    {
        $this->update([
            'seats_left' => max(((int) $this->seats_left) - $seats, 0),
        ]);
    }

    public function restoreSeats(int $seats): void
    {
        $this->update([
            'seats_left' => ((int) $this->seats_left) + $seats,
        ]);
    }
}