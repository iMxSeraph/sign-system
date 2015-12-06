<?php

namespace App\Console\Commands;

use DB;
use App\OldData;
use App\Record;
use Illuminate\Console\Command;

class TransferData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'routine:transfer-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '将108服务器的数据传输至109服务器';

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
        $count = 0;
        $rawdata = (OldData::where('Transfered', '0'));
        $data = $rawdata->get();
        $rawdata->update(['Transfered' => 1]);
        foreach ($data as $check)
        {
            $count++;
            Record::create(['nid' => $check->USERID, 'datetime' => strftime('%Y-%m-%d %X',strtotime($check->CHECKTIME))]);
        }
        //DB::connection('sqlsrv')->update('update CHECKINOUT set Transfered = 0');
    }
}
