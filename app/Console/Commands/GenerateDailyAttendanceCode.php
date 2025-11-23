<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateDailyAttendanceCode extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:generate-code';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a new daily attendance code';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $code = \App\Models\DailyAttendanceCode::createTodayCode();
        
        $this->info("Daily attendance code generated: {$code->code}");
        $this->info("Date: {$code->date->format('Y-m-d')}");
        
        return Command::SUCCESS;
    }
}
