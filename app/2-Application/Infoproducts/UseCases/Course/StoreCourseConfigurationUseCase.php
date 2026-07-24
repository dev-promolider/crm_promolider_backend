<?php

namespace Promolider\Application\Infoproducts\UseCases\Course;

use Promolider\Domain\Infoproducts\Ports\Out\CourseRepositoryInterface;

class StoreCourseConfigurationUseCase
{
    public function __construct(
        private CourseRepositoryInterface $courseRepository
    ) {}

    public function execute(array $request): array
    {
        $type = $this->determineType($request['type']);

        $data = $this->buildArray(
            $type,
            $request['course'],
            $request['module'] ?? null,
            $request['lesson'] ?? null,
            $request['type_certificate'],
            $request['certificate_price']
        );

        $courseConfiguration = $this->courseRepository->storeCourseConfiguration($data);

        if (!$courseConfiguration) {
            return [
                'success' => false,
                'message' => 'Failed to store course configuration.'
            ];
        }

        return [
            'success' => true,
            'message' => 'Course configuration stored successfully.'
        ];
    }

    private function determineType(string $number)
    {
        switch ($number) {
            case '1':
                $str = 'course';
                break;
            case '2':
                $str = 'module';
                break;
            case '3':
                $str = 'lesson';
                break;
            default:
                $str = '';
                break;
        }
        return $str;
    }

    private function buildArray(string $type, int $course_id, $module_id = null, $lesson_id = null, string $type_certificate, string $certificate_price)
    {
        if ($type_certificate == 1) {
            $certificate_price = 0;
        }

        switch ($type) {
            case 'course':
                $json = array(
                    'course' => $course_id,
                    'certificate_price' => $certificate_price

                );
                break;
            case 'module':
                $json = array(
                    'course' => $course_id,
                    'module' => $module_id,
                    'certificate_price' => $certificate_price
                );
                break;
            case 'lesson':
                $json = array(
                    'course' => $course_id,
                    'module' => $module_id,
                    'lesson' => $lesson_id,
                    'certificate_price' => $certificate_price
                );
                break;
            default:
                $json = '';
                break;
        }
        return $json;
    }
}
