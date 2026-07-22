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
            ]
            as $table
        ) {
            if (! Schema::hasTable($table)) {
                throw new \RuntimeException(
                    "Required table missing: {$table}",
                );
            }
        }

        foreach (
            [
                'from_airport_id',
                'to_airport_id',
                'return_airport_id',
            ]
            as $column
        ) {
            if (
                Schema::hasColumn(
                    'umrah_group_flights',
                    $column,
                )
            ) {
                throw new \RuntimeException(
                    "Unexpected existing column: {$column}",
                );
            }
        }

        Schema::table(
            'umrah_group_flights',
            function (Blueprint $table): void {
                $table
                    ->foreignId('from_airport_id')
                    ->nullable()
                    ->after('route');

                $table
                    ->foreignId('to_airport_id')
                    ->nullable()
                    ->after('from_airport_id');

                $table
                    ->foreignId('return_airport_id')
                    ->nullable()
                    ->after('to_airport_id');
            },
        );

        $this->backfillAirportLinks();

        Schema::table(
            'umrah_group_flights',
            function (Blueprint $table): void {
                $table
                    ->foreign('from_airport_id')
                    ->references('id')
                    ->on('airports')
                    ->restrictOnDelete();

                $table
                    ->foreign('to_airport_id')
                    ->references('id')
                    ->on('airports')
                    ->restrictOnDelete();

                $table
                    ->foreign('return_airport_id')
                    ->references('id')
                    ->on('airports')
                    ->restrictOnDelete();
            },
        );
    }

    public function down(): void
    {
        Schema::table(
            'umrah_group_flights',
            function (Blueprint $table): void {
                $table->dropForeign([
                    'from_airport_id',
                ]);

                $table->dropForeign([
                    'to_airport_id',
                ]);

                $table->dropForeign([
                    'return_airport_id',
                ]);

                $table->dropColumn([
                    'from_airport_id',
                    'to_airport_id',
                    'return_airport_id',
                ]);
            },
        );
    }

    private function backfillAirportLinks(): void
    {
        DB::table('umrah_group_flights')
            ->orderBy('id')
            ->get([
                'id',
                'from_city',
                'to_city',
                'return_city',
            ])
            ->each(
                function (object $row): void {
                    $updates = [];

                    foreach (
                        [
                            'from_city' =>
                                'from_airport_id',
                            'to_city' =>
                                'to_airport_id',
                            'return_city' =>
                                'return_airport_id',
                        ]
                        as $snapshotColumn =>
                            $idColumn
                    ) {
                        $value =
                            trim(
                                (string)
                                    ($row->{$snapshotColumn}
                                        ?? ''),
                            );

                        if ($value === '') {
                            continue;
                        }

                        $iata =
                            $this->resolveAlias(
                                $value,
                            );

                        if ($iata === null) {
                            throw new \RuntimeException(
                                "Unresolved airport snapshot {$snapshotColumn} on group flight {$row->id}: {$value}",
                            );
                        }

                        $matches =
                            DB::table('airports')
                                ->where('status', true)
                                ->whereRaw(
                                    'UPPER(iata_code) = ?',
                                    [$iata],
                                )
                                ->pluck('id')
                                ->unique()
                                ->values();

                        if ($matches->count() !== 1) {
                            throw new \RuntimeException(
                                "Ambiguous airport {$iata} for group flight {$row->id}.",
                            );
                        }

                        $updates[$idColumn] =
                            (int) $matches->first();
                    }

                    if ($updates !== []) {
                        DB::table(
                            'umrah_group_flights',
                        )
                            ->where(
                                'id',
                                $row->id,
                            )
                            ->update($updates);
                    }
                },
            );
    }

    private function resolveAlias(
        string $value,
    ): ?string {
        $aliases = [
            'MUX' => 'MUX',
            'MULTAN' => 'MUX',
            'LHE' => 'LHE',
            'LAHORE' => 'LHE',
            'JED' => 'JED',
            'JEDDHA' => 'JED',
            'JEDDAH' => 'JED',
            'MED' => 'MED',
            'MADINA' => 'MED',
            'MADINAH' => 'MED',
            'MEDINA' => 'MED',
        ];

        return $aliases[
            strtoupper(trim($value))
        ] ?? null;
    }
};
