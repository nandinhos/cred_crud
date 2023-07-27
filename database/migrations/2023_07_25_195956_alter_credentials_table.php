<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterCredentialsTable extends Migration
{
    public function up()
    {
        Schema::table('credentials', function (Blueprint $table) {
            $table->string('secrecy')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('credentials', function (Blueprint $table) {
            $table->string('secrecy')->nullable(false)->change();
        });
    }
}
