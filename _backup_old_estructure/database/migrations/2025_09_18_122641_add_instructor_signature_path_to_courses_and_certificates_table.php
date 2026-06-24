<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInstructorSignaturePathToCoursesAndCertificatesTable extends Migration
{
    public function up(): void
    {
        Schema::table('course_certificates', function (Blueprint $table) {
            $table->string('instructor_signature_path', 255)->nullable()->after('id');
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->string('instructor_signature_path', 255)->nullable()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('course_certificates', function (Blueprint $table) {
            $table->dropColumn('instructor_signature_path');
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn('instructor_signature_path');
        });
    }
}
