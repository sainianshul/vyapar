<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('patient_name', 100)->nullable()->after('cancellation_reason');
            $table->string('patient_age', 10)->nullable()->after('patient_name');
            $table->string('contact_phone', 20)->nullable()->after('patient_age');
            $table->string('secondary_phone', 20)->nullable()->after('contact_phone');
            $table->string('care_type_name', 100)->nullable()->after('secondary_phone');
            $table->text('address')->nullable()->after('care_type_name');
            $table->string('city', 100)->nullable()->after('address');
            $table->string('state', 100)->nullable()->after('city');
            $table->string('country', 100)->nullable()->after('state');
            $table->string('pincode', 20)->nullable()->after('country');
            $table->decimal('latitude', 10, 7)->nullable()->after('pincode');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'patient_name',
                'patient_age',
                'contact_phone',
                'secondary_phone',
                'care_type_name',
                'address',
                'city',
                'state',
                'country',
                'pincode',
                'latitude',
                'longitude',
            ]);
        });
    }
};
