<?php

namespace App\Traits;

use App\Models\User;
use App\Models\UserConfiguration;
use App\Models\CourseConfiguration;
use App\Http\Controllers\CourseController;

trait CourseProcessTrait
{
    public function processCourse($course)
    {
        if ($course->certificate == 1) {
            return $this->processCertificateCourse($course);
        } else {
            return $this->handleCourseStatus($course);
        }
    }

    public function processCertificateCourse($course)
    {
        $coursIsConfig = CourseConfiguration::where('course_id', $course->id)->exists();

        if (!$coursIsConfig) {
            return 'misconfigured';
        }

        $signatureConfig = $this->getUserConfigurationCount($course->user_id, 2);
        $templateConfig = $this->getUserConfigurationCount($course->user_id, 1);

        if ($signatureConfig > 0 && $templateConfig > 0) {
            return $this->handleCourseStatus($course);
        } else {
            return 'signaturetemplate';
        }
    }

    public function handleCourseStatus($course)
    {
        if ($course->status == 0 || $course->status == 3 || $course->status == 4) {
            $course->status = 1;

            if ($course->update()) {
                $this->notifyAdmins($course->title);
                return 'ok';
            } else {
                return 'error';
            }
        } else {
            return 'request';
        }
    }

    public function getUserConfigurationCount($userId, $configId)
    {
        return UserConfiguration::where('user_id', $userId)->where('configuration_id', $configId)->count();
    }

    public function notifyAdmins($courseTitle)
    {
        $adminList = User::where('id_account_type', 1)->get();

        foreach ($adminList as $admin) {
            $title = 'Curso pendiente a revisar';
            $body = "Tiene pendiente revisar $courseTitle";
            app(CourseController::class)->notification($admin->id, $title, $body);
        }
    }
}