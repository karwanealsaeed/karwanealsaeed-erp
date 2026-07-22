<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (
            [
                'airports',
                'umrah_group_flights',
                'umrah_packages',
            ]
            as $table
        ) {
            if (! Schema::hasTable($table)) {
                throw new \RuntimeException(
                    "Required table missing: {$table}",
                );
            }
        }

        if (
            ! Schema::hasColumn(
                'umrah_group_flights',
                'from_airport_id',
            )
        ) {
            throw new \RuntimeException(
                'Required group-flight from_airport_id is missing.',
            );
        }

        if (
            Schema::hasColumn(
                'umrah_packages',
                'departure_airport_id',
            )
        ) {
            throw new \RuntimeException(
                'Unexpected existing column: departure_airport_id',
            );
        }

        Schema::table(
            'umrah_packages',
            function (Blueprint $table): void {
                $table
                    ->foreignId('departure_airport_id')
                    ->nullable()
                    ->after('umrah_group_flight_id');
            },
        );

        $this->backfillLinkedPackages();

        Schema::table(
            'umrah_packages',
            function (Blueprint $table): void {
                $table
                    ->foreign('departure_airport_id')
                    ->references('id')
                    ->on('airports')
                    ->restrictOnDelete();
            },
        );
    }

    public function down(): void
    {
        Schema::table(
            'umrah_packages',
            function (Blueprint $table): void {
                $table->dropForeign([
                    'departure_airport_id',
                ]);

                $table->dropColumn(
                    'departure_airport_id',
                );
            },
        );
    }

    private function backfillLinkedPackages(): void
    {
        DB::table('umrah_packages')
            ->whereNotNull(
                'umrah_group_flight_id',
            )
            ->orderBy('id')
            ->get([
                'id',
                'umrah_group_flight_id',
            ])
            ->each(
                function (object $package): void {
                    $group =
                        DB::table(
                            'umrah_group_flights',
                        )
                            ->where(
                                'id',
                                $package
                                    ->umrah_group_flight_id,
                            )
                            ->first([
                                'id',
                                'from_airport_id',
                            ]);

                    if (! is_object($group)) {
                        throw new \RuntimeException(
                            "Linked group flight missing for package {$package->id}.",
                        );
                    }

                    if (
                        empty($group->from_airport_id)
                    ) {
                        throw new \RuntimeException(
                            "Group flight {$group->id} has no departure airport for package {$package->id}.",
                        );
                    }

                    $airportMatches =
                        DB::table('airports')
                            ->where(
                                'id',
                                $group->from_airport_id,
                            )
                            ->where('status', true)
                            ->pluck('id');

                    if (
                        $airportMatches->count()
                        !== 1
                    ) {
                        throw new \RuntimeException(
                            "Group flight {$group->id} has an invalid departure airport for package {$package->id}.",
                        );
                    }

                    DB::table('umrah_packages')
                        ->where(
                            'id',
                            $package->id,
                        )
                        ->update([
                            'departure_airport_id' =>
                                (int)
                                    $airportMatches
                                        ->first(),
                        ]);
                },
            );

        $standaloneWithAirport =
            DB::table('umrah_packages')
                ->whereNull(
                    'umrah_group_flight_id',
                )
                ->whereNotNull(
                    'departure_airport_id',
                )
                ->count();

        if ($standaloneWithAirport !== 0) {
            throw new \RuntimeException(
                'Standalone packages were unexpectedly assigned a departure airport.',
            );
        }
    }
};
