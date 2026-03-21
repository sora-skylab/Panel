<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

class AddCaptchaProviderSetting extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $provider = DB::table('settings')->where('key', 'settings::recaptcha:provider')->value('value');
        if ($provider !== null) {
            return;
        }

        $enabled = DB::table('settings')->where('key', 'settings::recaptcha:enabled')->value('value');
        $normalized = filter_var($enabled, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        DB::table('settings')->updateOrInsert(
            ['key' => 'settings::recaptcha:provider'],
            ['value' => $normalized === false ? 'none' : 'recaptcha']
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('settings')->where('key', 'settings::recaptcha:provider')->delete();
    }
}
