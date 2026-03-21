<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

class SetJapaneseAsDefaultPanelLocale extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $current = DB::table('settings')->where('key', 'settings::app:locale')->value('value');

        if ($current === null || $current === 'en') {
            DB::table('settings')->updateOrInsert(
                ['key' => 'settings::app:locale'],
                ['value' => 'ja']
            );

            DB::table('users')->where('language', 'en')->update(['language' => 'ja']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $current = DB::table('settings')->where('key', 'settings::app:locale')->value('value');

        if ($current === 'ja') {
            DB::table('settings')->updateOrInsert(
                ['key' => 'settings::app:locale'],
                ['value' => 'en']
            );
        }
    }
}
