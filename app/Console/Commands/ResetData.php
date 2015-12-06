<?php

namespace App\Console\Commands;

use DB;
use Illuminate\Console\Command;

class ResetData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'routine:reset-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '将108服务器上所有数据标记为未传输';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        DB::connection('sqlsrv')->update('update CHECKINOUT set Transfered = 0');
    }
}
