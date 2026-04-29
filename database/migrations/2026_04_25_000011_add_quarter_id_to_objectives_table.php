<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddQuarterIdToObjectivesTable extends Migration
{
    public function up()
    {
        Schema::table('objectives', function (Blueprint $table) {
            $table->foreignId('quarter_id')->nullable()->after('remi_id')->constrained('quarters')->nullOnDelete();
            $table->index(['company_id', 'quarter_id']);
        });

        /**
         * Migra data histórica de objectives.quarter (texto) a quarter_id si ya existían registros.
         */
        $rows = DB::table('objectives')
            ->select('company_id', 'quarter')
            ->whereNotNull('quarter')
            ->where('quarter', '!=', '')
            ->distinct()
            ->get();

        foreach ($rows as $row) {
            $quarter_name = (string) $row->quarter;
            $parts = explode('-', $quarter_name);
            $position = 1;
            $year = (int) date('Y');

            if (count($parts) === 2) {
                $position = (int) str_replace('Q', '', strtoupper($parts[0]));
                $year = (int) $parts[1];
            }

            $quarter_id = DB::table('quarters')->where([
                'company_id' => (int) $row->company_id,
                'name' => $quarter_name,
            ])->value('id');

            if (! $quarter_id) {
                $quarter_id = DB::table('quarters')->insertGetId([
                    'company_id' => (int) $row->company_id,
                    'name' => $quarter_name,
                    'year' => $year,
                    'position' => max(1, min(4, $position)),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('objectives')
                ->where('company_id', (int) $row->company_id)
                ->where('quarter', $quarter_name)
                ->update(['quarter_id' => $quarter_id]);
        }
    }

    public function down()
    {
        Schema::table('objectives', function (Blueprint $table) {
            $table->dropForeign(['quarter_id']);
            $table->dropIndex(['company_id', 'quarter_id']);
            $table->dropColumn('quarter_id');
        });
    }
}
