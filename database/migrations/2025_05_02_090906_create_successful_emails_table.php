<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('successful_emails', function (Blueprint $table) {
        $table->id();
        $table->mediumInteger('affiliate_id');
        $table->text('envelope');
        $table->string('from');
        $table->text('subject');
        $table->string('dkim')->nullable();
        $table->string('SPF')->nullable();
        $table->float('spam_score')->nullable();
        $table->longText('email'); // email brut
        $table->longText('raw_text')->nullable(); // text curat
        $table->string('sender_ip', 50)->nullable();
        $table->text('to');
        $table->integer('timestamp');
        $table->softDeletes();
        $table->timestamps();
    });
}

    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('successful_emails');
    }
};
