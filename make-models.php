<?php
$models = [
    'Wallet' => 'wallet',
    'Payment' => 'payments',
    'Classified' => 'classified',
    'Notifications' => 'notifications',
    'UserDailyQuizz' => 'user_daily_quizzs',
    'UserClassroomPoint' => 'user_classroom_points'
];

foreach ($models as $class => $table) {
    $content = "<?php\n\nnamespace App\\Models;\n\nuse Illuminate\\Database\\Eloquent\\Factories\\HasFactory;\nuse Illuminate\\Database\\Eloquent\\Model;\n\nclass $class extends Model\n{\n    use HasFactory;\n\n    protected \$table = '$table';\n    protected \$guarded = [];\n}\n";
    file_put_contents(__DIR__ . '/app/Models/' . $class . '.php', $content);
}
echo "Models created!";
