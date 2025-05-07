use Illuminate\Console\Scheduling\Schedule;

protected $commands = [
    \App\Console\Commands\ParseEmails::class,
];


protected function schedule(Schedule $schedule)
{
    $schedule->command('emails:parse')
             ->everyMinute() // temporar, se poate schimba
             ->appendOutputTo(storage_path('logs/cron-test.log'));
}

