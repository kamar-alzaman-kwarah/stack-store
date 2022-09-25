<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->double('price');
            $table->double('current_price')->default(null);
            $table->boolean('like')->default(false);
            $table->longText('details');
            $table->text('image_url');
            $table->integer('quantity')->default('1');
            $table->integer('views')->default('0');
            $table->date('expiration_date');
            //$table->integer('period_one');
            //$table->integer('discounts_one');
            //$table->integer('period_two');
            //$table->integer('discounts_two');
            //$table->integer('discounts_three');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
}
