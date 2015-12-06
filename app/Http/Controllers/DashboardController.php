<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Auth\Authenticatable;
use App\Record;
use App\User;
use App\Complain;
use Validator;
use Mail;
use App\Workday;

class DashboardController extends Controller
{
    /**
     * 默认界面
     *
     * @return \Illuminate\Http\Response
     */
    public function getIndex(Authenticatable $user)
    {
        // 工作日判断
        $isWorkday = $this->isWorkday(time());
        // 正式环境
        $overResult = $this->calculateOvertime($user, date('Y-m-d'), $isWorkday);
        $lateResult = $this->calculateLatetime($user, $overResult['firstSignIn'], date('Y-m-d'), $isWorkday, '09:30:00');

        // 测试环境
        // $overResult = $this->calculateOvertime($user, date('2015-09-25'), true);
        // $lateResult = $this->calculateLatetime($overResult['firstSignIn'], date('2015-09-25'), true);

        // 判断是否已经申辩
        $complain = Complain::where('type', 'late')->where('eid', $user->eid)->where('startdate', date('Y-m-d'))->first();
        if ($complain != null) {
            $isComplained = true;
        } else {
            $isComplained = false;
        }
        // 获取所有申辩
        $complains = Complain::where('eid', $user->eid)->get()->all();
        // return $lateResult;

        return view('dashboard')->withUser($user)->with('firstSignIn', $overResult['firstSignIn'])->with('lastSignIn', $overResult['lastSignIn'])->withOvertime($overResult['overtime'])
            ->with('isLate', $lateResult['isLate'])->withLatetime($lateResult['latetime'])->with('isComplained', $isComplained)->withComplains($complains)
            ->with('isWorkday', $isWorkday);
    }

    public function test() {
    	return bcrypt('12345678');
    }

    public function postIndex(Request $request, Authenticatable $user) {
        // 验证是否已经申诉过
        switch ($request->input('type')) {
            case 'complain':
                $complain = Complain::where('type', 'late')->where('eid', $user->eid)->where('startdate', date('Y-m-d'))->first();
                if ($complain != null) {
                    return redirect()->back()->withInput()->withErrors('请勿重复申辩');
                }

                // 验证有效性
                $v = $this->validatorComplain($request->all());
                if ($v->fails()) {
                    switch ($v->messages()->first()) {
                        case 'The description field is required.':
                            $messages = '理由至少需要5个字';
                            break;

                        default:
                            $messages = $v->messages()->first();
                            break;
                    }
                    return redirect()->back()->withInput()->withErrors($messages);
                }
                // 执行插入
                Complain::create(['type' => 'late', 'eid' => $user->eid, 'startdate' => date('Y-m-d'), 'enddate' => date('Y-m-d'), 'description' => $request->input('description') ,'token' => str_random(60)]);
                return redirect('dashboard')->with('success', '申辩已提交，请等待管理员审核。审核成功后会自动记录。');
                break;

            case 'workday':
                if (!$user->employee->admin) {
                    return redirect('dashboard');
                }

                // 验证有效性
                $v = $this->validatorWorkday($request->all());
                if ($v->fails()) {
                    switch ($v->messages()->first()) {
                        case 'The startdate field is required.':
                            $messages = '开始日期不能为空';
                            break;

                        case 'The startdate does not match the format Y-m-d.':
                            $messages = '开始日期不符合规则';
                            break;

                        case 'The enddate field is required.':
                            $messages = '结束日期不能为空';
                            break;

                        case 'The enddate does not match the format Y-m-d.':
                            $messages = '结束日期不符合规则';
                            break;

                        default:
                            $messages = $v->messages()->first();
                            break;
                    }
                    return redirect()->back()->withInput()->withErrors($messages);
                }

                // 判断冲突
                $count = Workday::where('date', '>=', $request->input('startdate'))->where('date', '<=', $request->input('enddate'))->count();
                if ($count > 0) {
                    return redirect()->back()->withInput()->withErrors('与已有记录冲突');
                }
                // 执行插入
                for ($i = strtotime($request->input('startdate')); $i <= strtotime($request->input('enddate')); $i += 86400) {
                    Workday::create(['date' => date('Y-m-d', $i), 'isworkday' => $request->input('isworkday')]);
                }
                return redirect('dashboard')->with('success', '特殊规则已记录。');
                break;

            case "result":
                // 验证有效性
                $v = $this->validatorResult($request->all());
                if ($v->fails()) {
                    switch ($v->messages()->first()) {
                        case 'The startdate field is required.':
                            $messages = '开始日期不能为空';
                            break;

                        case 'The startdate does not match the format Y-m-d.':
                            $messages = '开始日期不符合规则';
                            break;

                        case 'The enddate field is required.':
                            $messages = '结束日期不能为空';
                            break;

                        case 'The enddate does not match the format Y-m-d.':
                            $messages = '结束日期不符合规则';
                            break;

                        default:
                            $messages = $v->messages()->first();
                            break;
                    }
                    return redirect()->back()->withInput()->withErrors($messages);
                }

                $results = array();
                $count = 0;
                $overtimeSum = 0;
                $latetimeSum = 0;
                // 执行查询
                for ($i = strtotime($request->input('startdate')); $i <= strtotime($request->input('enddate')); $i += 86400) {
                    $temp = array();
                    // 日期
                    $temp['date'] = date('Y-m-d', $i);
                    // 类型
                    $isWorkday = $this->isWorkday($i);
                    $temp['type'] = $isWorkday ? true : false;
                    // 计算签到签退时间
                    $overResult = $this->calculateOvertime($user, date('Y-m-d', $i), $isWorkday);
                    $lateResult = $this->calculateLatetime($user, $overResult['firstSignIn'], date('Y-m-d', $i), $isWorkday, '09:30:00');

                    $temp['firstSignIn'] = $overResult['firstSignIn'];
                    $temp['lastSignIn'] = $overResult['lastSignIn'];
                    if ($temp['firstSignIn'] != null && $temp['lastSignIn'] == null) {
                        $temp['lastSignIn'] = date('Y-m-d', $i).' 18:00:00';
                    }
                    $temp['overtime'] = $overResult['overtime'];
                    $temp['latetime'] = $lateResult['latetime'];

                    $overtimeSum += $temp['overtime'];
                    if ($temp['latetime'] > 0) {
                        $latetimeSum++;
                    }
                    $results[$count] = $temp;
                    $count++;
                }

                // test
                // return $results;
                return view('result')->withResults($results)->with('overtimeSum', $overtimeSum)->with('latetimeSum', $latetimeSum);
                break;

            case "export":
                // 验证有效性
                $v = $this->validatorResult($request->all());
                if ($v->fails()) {
                    switch ($v->messages()->first()) {
                        case 'The startdate field is required.':
                            $messages = '开始日期不能为空';
                            break;

                        case 'The startdate does not match the format Y-m-d.':
                            $messages = '开始日期不符合规则';
                            break;

                        case 'The enddate field is required.':
                            $messages = '结束日期不能为空';
                            break;

                        case 'The enddate does not match the format Y-m-d.':
                            $messages = '结束日期不符合规则';
                            break;

                        default:
                            $messages = $v->messages()->first();
                            break;
                    }
                    return redirect()->back()->withInput()->withErrors($messages);
                }

                // 计算加班
                $overResults = array();
                $overCount = 0;
                $lateResults = array();
                $lateCount = 0;
                $okResults = array();
                $okCount = 0;
                // 执行查询
                $users  = User::all();
                foreach ($users as $u) {
                    for ($i = strtotime($request->input('startdate')); $i <= strtotime($request->input('enddate')); $i += 86400) {
                        // 计算签到签退时间
                        $isWorkday = $this->isWorkday($i);
                        $overResult = $this->calculateOvertime($u, date('Y-m-d', $i), $isWorkday);
                        $lateResult = $this->calculateLatetime($u, $overResult['firstSignIn'], date('Y-m-d', $i), $isWorkday, '09:30:00');
                        $okResult = $this->calculateLatetime($u, $overResult['firstSignIn'], date('Y-m-d', $i), $isWorkday, '09:00:00');

                        if ($overResult['overtime'] > 0) {
                            // 生成加班列表
                            $temp = array();
                            // 序号
                            $temp['id'] = $overCount + 1;
                            // 工号
                            $temp['eid'] = $u->eid;
                            // 姓名
                            $temp['name'] = $u->employee->name;
                            // 日期
                            $temp['date'] = date('Y-m-d', $i);
                            $temp['overtime'] = $overResult['overtime'];
                            $overResults[$overCount] = $temp;
                            $overCount++;
                        }

                        if ($lateResult['latetime'] > 0) {
                            // 生成迟到列表
                            $temp = array();
                            // 序号
                            $temp['id'] = $lateCount + 1;
                            // 工号
                            $temp['eid'] = $u->eid;
                            // 姓名
                            $temp['name'] = $u->employee->name;
                            // 日期
                            $temp['date'] = date('Y-m-d', $i);
                            $temp['latetime'] = $lateResult['latetime'];
                            $lateResults[$lateCount] = $temp;
                            $lateCount++;
                        }

                        if ($okResult['latetime'] > 0) {
                            // 生成晚到列表
                            $temp = array();
                            // 序号
                            $temp['id'] = $okCount + 1;
                            // 工号
                            $temp['eid'] = $u->eid;
                            // 姓名
                            $temp['name'] = $u->employee->name;
                            // 日期
                            $temp['date'] = date('Y-m-d', $i);
                            $temp['latetime'] = $okResult['latetime'];
                            $okResults[$okCount] = $temp;
                            $okCount++;
                        }
                    }
                }
                // test
                // return $results;
                return view('export')->with('overResults', $overResults)->with('lateResults', $lateResults)->with('okResults', $okResults);
                break;

            // 其它申辩
            case 'business':
                $v = $this->validatorBusiness($request->all());
                if ($v->fails()) {
                    switch ($v->messages()->first()) {
                        case 'The startdate field is required.':
                            $messages = '开始日期不能为空';
                            break;

                        case 'The startdate does not match the format Y-m-d.':
                            $messages = '开始日期不符合规则';
                            break;

                        case 'The enddate field is required.':
                            $messages = '结束日期不能为空';
                            break;

                        case 'The enddate does not match the format Y-m-d.':
                            $messages = '结束日期不符合规则';
                            break;

                        case 'The description field is required.':
                            $messages = '理由至少需要5个字';
                            break;

                        default:
                            $messages = $v->messages()->first();
                            break;
                    }
                    return redirect()->back()->withInput()->withErrors($messages);
                }

                if ($request->input('businessType') != 'business' && $request->input('businessType') != 'ask'){
                    return redirect()->back()->withInput()->withErrors('申辩类型非法');
                }

                if (strtotime($request->input('startdate')) > strtotime($request->input('enddate'))) {
                    return redirect()->back()->withInput()->withErrors('起始时间大于结束时间');
                }

                // 判断该时间段是否是旷工，只有旷工才可进行此类申辩
                for ($i = strtotime($request->input('startdate')); $i <= strtotime($request->input('enddate')); $i += 86400) {
                    $isWorkday = $this->isWorkday($i);
                    $overResult = $this->calculateOvertime($user, date('Y-m-d', $i), $isWorkday);
                    $lateResult = $this->calculateLatetime($user, $overResult['firstSignIn'], date('Y-m-d', $i), $isWorkday, '09:30:00');
                    if ($lateResult['latetime'] != 540) {
                        return redirect()->back()->withInput()->withErrors('你在 '.date('Y-m-d', $i).' 并未旷工，不符合申辩要求');
                    }
                }

                Complain::create(['type' => $request->input('businessType'), 'eid' => $user->eid, 'startdate' => $request->input('startdate'), 'enddate' => $request->input('enddate'), 'description' => $request->input('description') ,'token' => str_random(60)]);
                return redirect('dashboard')->with('success', '申辩已提交，请等待管理员审核。审核成功后会自动记录。');

                break;

            default:
                return redirect('dashboard');
                break;
        }
    }

    public function getAdmin(Authenticatable $user) {
        if (!$user->employee->admin) {
            return redirect('dashboard');
        }
        $complains = Complain::orderBy('created_at', 'desc')->take(15)->get();
        return view('admin')->withComplains($complains);
    }

    public function getConfirm(Request $request, Authenticatable $user, $token) {
        // 处理审核操作
        if (!$user->employee->admin) {
            return redirect('dashboard');
        }
        $complain = Complain::where('token', $token)->first();
        if ($complain == null) {
            return redirect('dashboard')->withErrors('找不到该条信息');
        }
        if ($request->input('reject')) {
            // 拒绝审核
            $complain->state = 'rejected';
            $messages = '已成功拒绝请求';
        } else {
            // 通过审核
            $complain->state = 'confirmed';
            $messages = '已成功通过请求';
        }
        $complain->operator = $user->employee->name;
        $complain->save();
        return redirect('admin')->with('success', $messages);
    }

    protected function validatorComplain(array $data)
    {
        return Validator::make($data, [
            'description' => 'required|max:255|min:5'
        ]);
    }

    protected function validatorBusiness(array $data)
    {
        return Validator::make($data, [
            'description' => 'required|max:255|min:5',
            'startdate' => 'required|date_format:Y-m-d',
            'enddate' => 'required|date_format:Y-m-d',
        ]);
    }

    protected function validatorWorkday(array $data)
    {
        return Validator::make($data, [
            'startdate' => 'required|date_format:Y-m-d',
            'enddate' => 'required|date_format:Y-m-d',
            'isworkday' => 'required|boolean'
        ]);
    }

    protected function validatorResult(array $data)
    {
        return Validator::make($data, [
            'startdate' => 'required|date_format:Y-m-d',
            'enddate' => 'required|date_format:Y-m-d',
        ]);
    }

    public function isWorkday($timestamp) {
        $workday = Workday::where('date', date('Y-m-d', $timestamp))->first();
        if ($workday) {
            return $workday->isworkday;
        }
        if (getdate($timestamp)['weekday'] != 'Sunday' && getdate($timestamp)['weekday'] != 'Saturday') {
            $isWorkday = true;
        } else {
            $isWorkday = false;
        }
        return $isWorkday;
    }
    /**
     * 计算某用户在某天的签到时间、签退时间和加班时长
     *
     * @param $user User
     * @param $date string
     * @param $workday boolean
     * @return $array['firstSignIn', 'lastSignIn', 'overtime']
     */
    public function calculateOvertime($user, $date, $isWorkday) {
        // 初始化参数
        $overtime = 0;
        $firstSignIn = null;
        $lastSignIn = null;
        if ($isWorkday) {
            // 工作日
            // 获取当日最早签到记录
            $records = $user->employee->records()->where('datetime', '>', $date.' 07:00:00')->where('datetime', '<', date('Y-m-d', strtotime($date) + 86400))->first();
            if ($records != null) {
                $firstSignIn = $records->datetime;
                if (strtotime($firstSignIn) < strtotime($date.'08:50:00')) {
                    $overtime += strtotime($date.' 09:00:00') - strtotime($firstSignIn);
                }
            }

            // 获取签退记录
            $records = $user->employee->records()->where('datetime', '>', $date.' 18:00:00')->where('datetime', '<', date('Y-m-d', strtotime($date) + 86400))->get()->all();
            if ($records != null) {
                $lastSignIn = end($records)->datetime;
                if (strtotime($lastSignIn) > strtotime($date.' 18:40:00')) {
                    $overtime +=  strtotime($lastSignIn) - strtotime($date.' 18:20:00');
                }
            } else {
                if (getdate()['hours'] >= 18 && $firstSignIn != null) {
                    $lastSignIn = $date.' 18:00:00';
                }
            }
        } else {
            // 非工作日
            // 获取当日最早签到记录
            $records = $user->employee->records()->where('datetime', '>', $date)->where('datetime', '<', date('Y-m-d', strtotime($date) + 86400))->first();
            if ($records != null) {
                $firstSignIn = $records->datetime;
            }

            // 获取签退记录
            $records = $user->employee->records()->where('datetime', '>', $date)->where('datetime', '<', date('Y-m-d', strtotime($date) + 86400))->get()->all();
            if ($records != null) {
                $lastSignIn = end($records)->datetime;
            }

            if ($firstSignIn != null && $lastSignIn != null) {
                $overtime += strtotime($lastSignIn) - strtotime($firstSignIn);
            }

            if ($overtime < 20 * 60) {
                $overtime = 0;
            }
        }
        // 计算加班时数
        $overtime = round($overtime / 60);
        return array('firstSignIn' => $firstSignIn, 'lastSignIn' => $lastSignIn, 'overtime' => $overtime);
    }

    /**
     * 计算某用户在某天是否迟到，以及迟到时长
     *
     * @param $firstSignIn string
     * @param $date string
     * @param $isWorkday boolean
     * @return $array['isLate', 'lasttime']
     */
    public function calculateLatetime($user, $firstSignIn, $date, $isWorkday, $standard) {
        // 如果有申诉并且通过则今天绝不会迟到
        $complain = $user->complains()->where('startdate', '<=', $date)->where('enddate', '>=', $date)->where('state', 'confirmed')->first();
        if ($complain) {
            return array('isLate' => false, 'latetime' => -1);
        }
        // 迟到时数
        $isLate = false;
        $latetime = 0;
        if ($isWorkday) {
            // 工作日
            if (!$firstSignIn && time() > strtotime($date.' 09:30:00')) {
                // 没有签到信息并且当前时间过了9点30分
                $isLate = true;
                $latetime = time() - strtotime($date.' 09:00:00') > 540 * 60 ? 540 : ceil((time() - strtotime($date.' 09:00:00')) / 60);
            } else if (strtotime($firstSignIn) > strtotime($date.' '.$standard)) {
                // 迟到
                $isLate = true;
                $latetime = ceil((strtotime($firstSignIn) - strtotime($date.' 09:00:00')) / 60) > 540 ? 540 : ceil((strtotime($firstSignIn) - strtotime($date.' 09:00:00')) / 60);
            } else {
                // 没迟到
                $isLate = false;
                $latetime = 0;
            }
        } else {
            // 非工作日
            $isLate = false;
        }
        // 计算迟到时数
        return array('isLate' => $isLate, 'latetime' => $latetime);
    }
}
