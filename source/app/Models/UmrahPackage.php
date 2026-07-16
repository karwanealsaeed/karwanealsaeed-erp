<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class UmrahPackage extends Model
{
    protected $fillable = [
        'package_no',
        'group_code',
        'umrah_group_flight_id',
        'title',
        'slug',
        'subtitle',
        'package_image',
        'package_type',
        'departure_code',
        'arrival_code',
        'departure_city',
        'departure_at',
        'arrival_at',
        'airline',
        'flight_route',
        'seats_available',
        'seats_left',
        'duration_days',
        'duration_nights',
        'makkah_nights',
        'madina_nights',
        'makkah_hotel',
        'makkah_hotel_id',
        'makkah_distance',
        'makkah_location',
        'madina_hotel',
        'madina_hotel_id',
        'madina_distance',
        'madina_location',
        'transport',
        'umrah_transport_route_id',
        'sharing_type',
        'price',
        'sharing_price',
        'quad_price',
        'triple_price',
        'double_price',
        'child_price',
        'infant_price',
        'start_date',
        'end_date',
        'description',
        'inclusions',
        'exclusions',
        'included_services',
        'other_included_service',
        'gifted_services',
        'status',
        'is_featured',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sharing_price' => 'decimal:2',
        'quad_price' => 'decimal:2',
        'triple_price' => 'decimal:2',
        'double_price' => 'decimal:2',
        'child_price' => 'decimal:2',
        'infant_price' => 'decimal:2',
        'departure_at' => 'datetime',
        'arrival_at' => 'datetime',
        'start_date' => 'date',
        'end_date' => 'date',
        'included_services' => 'array',
        'gifted_services' => 'array',
        'is_featured' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (UmrahPackage $package): void {
            if (blank($package->package_no) || $package->package_no === 'UP-') {
                $nextId = ((int) self::max('id')) + 1;
                $package->package_no = 'UP-' . str_pad((string) $nextId, 5, '0', STR_PAD_LEFT);
            }
        });

        static::saving(function (UmrahPackage $package): void {
            if ($package->umrahGroupFlight) {
                $group = $package->umrahGroupFlight;

                $package->group_code = $group->group_code;
                $package->airline = $group->airline;
                $package->flight_route = $group->route;
                $package->departure_city = $group->from_city;
                $package->start_date = $group->departure_date;
                $package->end_date = $group->return_date;
                $package->duration_days = $group->duration_days;
                $package->duration_nights = $group->duration_nights;
                $package->seats_available = $group->seats_total;
                $package->seats_left = $group->seats_left;
            }

            if ($package->makkahHotel) {
                $hotel = $package->makkahHotel;

                $package->makkah_hotel = trim($hotel->hotel_name . ' ' . ($hotel->similar_text ?: ''));
                $package->makkah_distance = $hotel->distance;
                $package->makkah_location = $hotel->location;
            }

            if ($package->madinaHotel) {
                $hotel = $package->madinaHotel;

                $package->madina_hotel = trim($hotel->hotel_name . ' ' . ($hotel->similar_text ?: ''));
                $package->madina_distance = $hotel->distance;
                $package->madina_location = $hotel->location;
            }

            if ($package->transportRoute) {
                $package->transport = $package->transportRoute->route_description ?: $package->transportRoute->title;
            }

            $package->price = $package->sharing_price ?: 0;

            if (blank($package->slug)) {
                $baseSlug = Str::slug($package->title . '-' . $package->package_no);
                $slug = $baseSlug;
                $counter = 1;

                while (
                    self::query()
                        ->where('slug', $slug)
                        ->when($package->exists, fn ($query) => $query->where('id', '!=', $package->id))
                        ->exists()
                ) {
                    $slug = $baseSlug . '-' . $counter;
                    $counter++;
                }

                $package->slug = $slug;
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function umrahGroupFlight(): BelongsTo
    {
        return $this->belongsTo(UmrahGroupFlight::class);
    }

    public function makkahHotel(): BelongsTo
    {
        return $this->belongsTo(UmrahHotel::class, 'makkah_hotel_id');
    }

    public function madinaHotel(): BelongsTo
    {
        return $this->belongsTo(UmrahHotel::class, 'madina_hotel_id');
    }

    public function transportRoute(): BelongsTo
    {
        return $this->belongsTo(UmrahTransportRoute::class, 'umrah_transport_route_id');
    }

    public function getFormattedPriceAttribute(): string
    {
        return 'PKR ' . number_format((float) ($this->sharing_price ?: $this->price), 0);
    }

    public function getNightsTextAttribute(): string
    {
        if ($this->duration_nights) {
            return $this->duration_nights . ' Night(s)';
        }

        return '-';
    }

    public function getIncludedServicesTextAttribute(): string
    {
        $services = $this->included_services ?: [];

        if ($this->other_included_service) {
            $services[] = $this->other_included_service;
        }

        return count($services) > 0 ? implode(', ', $services) : '-';
    }

    public function getGiftedServicesTextAttribute(): string
    {
        $services = $this->gifted_services ?: [];

        return count($services) > 0 ? implode(', ', $services) : '-';
    }

    public function getPricesListAttribute(): array
    {
        return [
            'Sharing' => $this->sharing_price,
            'Quad' => $this->quad_price,
            'Triple' => $this->triple_price,
            'Double' => $this->double_price,
            'Child' => $this->child_price,
            'Infant' => $this->infant_price,
        ];
    }
}