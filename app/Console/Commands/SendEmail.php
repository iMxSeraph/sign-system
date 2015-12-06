<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\User;
use Mail;
use App\Http\Controllers\DashboardController;
use App\Complain;

class SendEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'routine:send-email';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '发送提醒邮件';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public $userData;
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // 提醒迟到用户申辩
        foreach (User::all() as $user) {
            $dashboardController = new DashboardController();
            $overResult = $dashboardController->calculateOvertime($user, date('Y-m-d'), $dashboardController->isWorkday(time()));
            $lateResult = $dashboardController->calculateLatetime($user, $overResult['firstSignIn'], date('Y-m-d'), $dashboardController->isWorkday(time()), '09:30:00');

            $count = $user->complains()->where('startdate', date('Y-m-d'))->where('state', '')->where('type', 'late')->count();

            if ($lateResult['isLate'] && $count == 0) {
                $this->userData = $user;
                Mail::send('emails.late', ['username' => $this->userData->employee->name], function($message) {
                    $message->from('hziflytek@126.com','移动互联签到系统');
                    $message->to($this->userData->employee->email, $this->userData->employee->name);
                    $message->subject('【迟到提醒】');
                });
                // $this->info($user->employee->name.'迟到了');
            }
        }

        // 提醒管理员处理申诉
        $count = Complain::where('state', '')->count();
        if ($count != 0) {
            foreach (User::all() as $user) {
                if ($user->employee->admin) {
                    $this->userData = $user;
                    Mail::send('emails.admin', ['username' => $this->userData->employee->name], function($message) {
                        $message->from('hziflytek@126.com','移动互联签到系统');
                        $message->to($this->userData->employee->email, $this->userData->employee->name);
                        $message->subject('【审核提醒】');
                    });
                    // $this->info('提醒'.$user->employee->name);
                }
            }
        }
    }
}
