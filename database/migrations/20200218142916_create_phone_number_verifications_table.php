<?php

use App\Support\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;

/**
 * PHPStorm helper
 * @property Builder schema
 * @property Blueprint table
 */
class CreatePhoneNumberVerificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema->create('phone_number_verifications', function (Blueprint $table) {
           $table->bigIncrements('id');
           $table->string('phone_number')->unique();
           $table->string('code');
           $table->timestamp('phone_number_verified_at')->nullable();
           $table->timestamps();
           $table->engine = 'InnoDB';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema->dropIfExists('phone_number_verifications');
    }
}
