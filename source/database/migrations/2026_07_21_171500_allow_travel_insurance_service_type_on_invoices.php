<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction =
        false;

    private const COLUMN =
        'service_type';

    private const SERVICE_TYPE =
        'Travel Insurance';

    public function up(): void
    {
        if (! Schema::hasTable('invoices')) {
            return;
        }

        if (
            ! Schema::hasColumn(
                'invoices',
                self::COLUMN,
            )
        ) {
            return;
        }

        $this->changeConstraint(
            includeTravelInsurance: true,
        );
    }

    public function down(): void
    {
        if (! Schema::hasTable('invoices')) {
            return;
        }

        if (
            ! Schema::hasColumn(
                'invoices',
                self::COLUMN,
            )
        ) {
            return;
        }

        if (
            DB::table('invoices')
                ->where(
                    self::COLUMN,
                    self::SERVICE_TYPE,
                )
                ->exists()
        ) {
            throw new RuntimeException(
                'Travel Insurance invoices mojood hain; service type constraint rollback nahi ho sakta.',
            );
        }

        $this->changeConstraint(
            includeTravelInsurance: false,
        );
    }

    private function changeConstraint(
        bool $includeTravelInsurance,
    ): void {
        $driver =
            DB::connection()
                ->getDriverName();

        if (
            in_array(
                $driver,
                [
                    'mysql',
                    'mariadb',
                ],
                true,
            )
        ) {
            $this->changeMySqlEnum(
                $includeTravelInsurance,
            );

            return;
        }

        if ($driver === 'sqlite') {
            $this->changeSqliteCheckConstraint(
                $includeTravelInsurance,
            );

            return;
        }

        throw new RuntimeException(
            'Invoices service_type constraint migration unsupported database driver par run hui: '
            .$driver,
        );
    }

    private function changeMySqlEnum(
        bool $includeTravelInsurance,
    ): void {
        $column =
            DB::selectOne(
                "SHOW FULL COLUMNS FROM `invoices` LIKE 'service_type'",
            );

        if (! $column) {
            throw new RuntimeException(
                'MySQL invoices.service_type column inspect nahi hui.',
            );
        }

        $type =
            (string) (
                $column->Type
                ?? $column->type
                ?? ''
            );

        if (
            ! preg_match(
                '/^enum\((.*)\)$/i',
                $type,
                $matches,
            )
        ) {
            throw new RuntimeException(
                'MySQL invoices.service_type expected ENUM nahi hai: '
                .$type,
            );
        }

        $values =
            str_getcsv(
                $matches[1],
                ',',
                "'",
                '\\',
            );

        $values =
            array_values(
                array_unique(
                    array_map(
                        static fn (
                            mixed $value,
                        ): string =>
                            trim(
                                (string) $value,
                            ),
                        $values,
                    ),
                ),
            );

        if ($includeTravelInsurance) {
            if (
                ! in_array(
                    self::SERVICE_TYPE,
                    $values,
                    true,
                )
            ) {
                $otherPosition =
                    array_search(
                        'Other',
                        $values,
                        true,
                    );

                if ($otherPosition === false) {
                    $values[] =
                        self::SERVICE_TYPE;
                } else {
                    array_splice(
                        $values,
                        $otherPosition,
                        0,
                        [
                            self::SERVICE_TYPE,
                        ],
                    );
                }
            }
        } else {
            $values =
                array_values(
                    array_filter(
                        $values,
                        static fn (
                            string $value,
                        ): bool =>
                            $value !==
                            self::SERVICE_TYPE,
                    ),
                );
        }

        if ($values === []) {
            throw new RuntimeException(
                'MySQL invoices.service_type ENUM values empty ho gayin.',
            );
        }

        $quotedValues =
            array_map(
                static function (
                    string $value,
                ): string {
                    $escaped =
                        str_replace(
                            [
                                '\\',
                                "'",
                            ],
                            [
                                '\\\\',
                                "\\'",
                            ],
                            $value,
                        );

                    return "'".$escaped."'";
                },
                $values,
            );

        $nullableValue =
            strtoupper(
                (string) (
                    $column->Null
                    ?? $column->null
                    ?? 'NO'
                ),
            );

        $nullable =
            $nullableValue === 'YES'
                ? 'NULL'
                : 'NOT NULL';

        $default =
            $column->Default
            ?? $column->default
            ?? null;

        $defaultSql =
            '';

        if ($default !== null) {
            $defaultSql =
                ' DEFAULT '
                .DB::connection()
                    ->getPdo()
                    ->quote(
                        (string) $default,
                    );
        } elseif ($nullable === 'NULL') {
            $defaultSql =
                ' DEFAULT NULL';
        }

        $comment =
            (string) (
                $column->Comment
                ?? $column->comment
                ?? ''
            );

        $commentSql =
            '';

        if ($comment !== '') {
            $commentSql =
                ' COMMENT '
                .DB::connection()
                    ->getPdo()
                    ->quote(
                        $comment,
                    );
        }

        $sql =
            'ALTER TABLE `invoices` '
            .'MODIFY COLUMN `service_type` ENUM('
            .implode(
                ', ',
                $quotedValues,
            )
            .') '
            .$nullable
            .$defaultSql
            .$commentSql;

        DB::statement(
            $sql,
        );
    }

    private function changeSqliteCheckConstraint(
        bool $includeTravelInsurance,
    ): void {
        $tableDefinition =
            DB::selectOne(
                "SELECT sql
                 FROM sqlite_master
                 WHERE type = 'table'
                   AND name = 'invoices'",
            );

        $originalCreateSql =
            (string) (
                $tableDefinition->sql
                ?? ''
            );

        if ($originalCreateSql === '') {
            throw new RuntimeException(
                'SQLite invoices table definition inspect nahi hui.',
            );
        }

        $pattern =
            '/(check\s*\(\s*["`]?service_type["`]?\s+in\s*\()([^)]+)(\)\s*\))/i';

        $matchCount =
            preg_match_all(
                $pattern,
                $originalCreateSql,
            );

        if ($matchCount !== 1) {
            throw new RuntimeException(
                'SQLite service_type CHECK constraint exactly once nahi mili. Count: '
                .$matchCount,
            );
        }

        $updatedCreateSql =
            preg_replace_callback(
                $pattern,
                function (
                    array $matches,
                ) use (
                    $includeTravelInsurance,
                ): string {
                    preg_match_all(
                        "/'((?:''|[^'])*)'/",
                        $matches[2],
                        $valueMatches,
                    );

                    $values =
                        array_map(
                            static fn (
                                string $value,
                            ): string =>
                                str_replace(
                                    "''",
                                    "'",
                                    $value,
                                ),
                            $valueMatches[1],
                        );

                    $values =
                        array_values(
                            array_unique(
                                $values,
                            ),
                        );

                    if ($includeTravelInsurance) {
                        if (
                            ! in_array(
                                self::SERVICE_TYPE,
                                $values,
                                true,
                            )
                        ) {
                            $otherPosition =
                                array_search(
                                    'Other',
                                    $values,
                                    true,
                                );

                            if ($otherPosition === false) {
                                $values[] =
                                    self::SERVICE_TYPE;
                            } else {
                                array_splice(
                                    $values,
                                    $otherPosition,
                                    0,
                                    [
                                        self::SERVICE_TYPE,
                                    ],
                                );
                            }
                        }
                    } else {
                        $values =
                            array_values(
                                array_filter(
                                    $values,
                                    static fn (
                                        string $value,
                                    ): bool =>
                                        $value !==
                                        self::SERVICE_TYPE,
                                ),
                            );
                    }

                    if ($values === []) {
                        throw new RuntimeException(
                            'SQLite service_type CHECK values empty ho gayin.',
                        );
                    }

                    $quoted =
                        array_map(
                            static fn (
                                string $value,
                            ): string =>
                                "'"
                                .str_replace(
                                    "'",
                                    "''",
                                    $value,
                                )
                                ."'",
                            $values,
                        );

                    return $matches[1]
                        .implode(
                            ', ',
                            $quoted,
                        )
                        .$matches[3];
                },
                $originalCreateSql,
                1,
                $replacementCount,
            );

        if ($replacementCount !== 1) {
            throw new RuntimeException(
                'SQLite service_type CHECK constraint replace nahi hui.',
            );
        }

        $temporaryTable =
            'invoices_service_type_rebuild';

        $temporaryCreateSql =
            preg_replace(
                '/^\s*create\s+table\s+["`]?invoices["`]?/i',
                'CREATE TABLE "'
                    .$temporaryTable
                    .'"',
                (string) $updatedCreateSql,
                1,
                $tableNameReplacementCount,
            );

        if ($tableNameReplacementCount !== 1) {
            throw new RuntimeException(
                'SQLite temporary Invoice table SQL create nahi hui.',
            );
        }

        $indexes =
            DB::select(
                "SELECT sql
                 FROM sqlite_master
                 WHERE type = 'index'
                   AND tbl_name = 'invoices'
                   AND sql IS NOT NULL",
            );

        $triggers =
            DB::select(
                "SELECT sql
                 FROM sqlite_master
                 WHERE type = 'trigger'
                   AND tbl_name = 'invoices'
                   AND sql IS NOT NULL",
            );

        $columnRows =
            DB::select(
                'PRAGMA table_info("invoices")',
            );

        $columns =
            array_map(
                static fn (
                    object $column,
                ): string =>
                    (string) $column->name,
                $columnRows,
            );

        if ($columns === []) {
            throw new RuntimeException(
                'SQLite Invoice columns inspect nahi huin.',
            );
        }

        $quotedColumns =
            array_map(
                static fn (
                    string $column,
                ): string =>
                    '"'
                    .str_replace(
                        '"',
                        '""',
                        $column,
                    )
                    .'"',
                $columns,
            );

        DB::statement(
            'PRAGMA foreign_keys = OFF',
        );

        DB::beginTransaction();

        try {
            DB::statement(
                'DROP TABLE IF EXISTS "'
                .$temporaryTable
                .'"',
            );

            DB::statement(
                (string) $temporaryCreateSql,
            );

            DB::statement(
                'INSERT INTO "'
                .$temporaryTable
                .'" ('
                .implode(
                    ', ',
                    $quotedColumns,
                )
                .') SELECT '
                .implode(
                    ', ',
                    $quotedColumns,
                )
                .' FROM "invoices"',
            );

            DB::statement(
                'DROP TABLE "invoices"',
            );

            DB::statement(
                'ALTER TABLE "'
                .$temporaryTable
                .'" RENAME TO "invoices"',
            );

            foreach ($indexes as $index) {
                $sql =
                    trim(
                        (string) (
                            $index->sql
                            ?? ''
                        ),
                    );

                if ($sql !== '') {
                    DB::statement(
                        $sql,
                    );
                }
            }

            foreach ($triggers as $trigger) {
                $sql =
                    trim(
                        (string) (
                            $trigger->sql
                            ?? ''
                        ),
                    );

                if ($sql !== '') {
                    DB::statement(
                        $sql,
                    );
                }
            }

            DB::commit();
        } catch (Throwable $exception) {
            DB::rollBack();

            throw $exception;
        } finally {
            DB::statement(
                'PRAGMA foreign_keys = ON',
            );
        }

        $violations =
            DB::select(
                'PRAGMA foreign_key_check',
            );

        if ($violations !== []) {
            throw new RuntimeException(
                'SQLite foreign-key validation Invoice table rebuild ke baad fail hui.',
            );
        }
    }
};