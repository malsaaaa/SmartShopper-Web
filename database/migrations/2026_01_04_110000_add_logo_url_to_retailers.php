<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('retailers', function (Blueprint $table) {
            $table->string('logo_url')->nullable()->after('base_url');
        });
    }

    public function down()
    {
        Schema::table('retailers', function (Blueprint $table) {
            $table->dropColumn('logo_url');
        });
    }
};
