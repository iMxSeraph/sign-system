<?php

namespace App\Http\Controllers;

use Auth;
use Validator;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\User;
use Mail;

class AuthController extends Controller
{
    public $userData;
    /**
     * 显示登录界面
     *
     * @return \Illuminate\Http\Response
     */
    public function getLogin()
    {
        return view('login');
    }

    /**
     * 处理登录过程
     *
     * @return \Illuminate\Http\Response
     */
    public function postLogin(Request $request)
    {
        // 验证登录信息
        $v = $this->validatorLogin($request->all());
        if ($v->fails()) {
            switch ($v->messages()->first()) {
                case 'The eid field is required.':
                    $messages = '工号不能为空';
                    break;

                case 'The password field is required.':
                    $messages = '密码不能为空';
                    break;
                
                default:
                    $messages = $v->messages()->first();
                    break;
            }
            return redirect()->back()->withErrors($messages);
        }
        // 执行登录
        if (Auth::attempt(['eid' => $request->input('eid'), 'password' => $request->input('password')], $request->input('remember')))
        {
            return redirect()->intended('dashboard');
        }
        return redirect()->back()->withErrors('工号/密码错误');
    }

    /**
     * 显示注册界面
     *
     * @return \Illuminate\Http\Response
     */
    public function getRegister()
    {
        return view('register');
    }

    /**
     * 处理注册过程
     *
     * @return \Illuminate\Http\Response
     */
    public function postRegister(Request $request)
    {
        // 验证输入有效性
        $v = $this->validatorRegister($request->all());
        if ($v->fails()) {
            switch ($v->messages()->first()) {
                case 'The eid field is required.':
                    $messages = '工号不能为空';
                    break;

                case 'The selected eid is invalid.':
                    $messages = '工号无效';
                    break;

                case 'The eid has already been taken.':
                    $messages = '该工号已被注册';
                    break;

                case 'The password field is required.':
                    $messages = '密码不能为空';
                    break;
                
                case 'The password confirmation does not match.':
                    $messages = '两次密码输入不一致';
                    break;
                    
                case 'The password must be at least 8 characters.':
                    $messages = '密码至少需要8位';
                    break;
                
                default:
                    $messages = $v->messages()->first();
                    break;
            }
            return redirect()->back()->withInput()->withErrors($messages);
        }
        // 进行注册
        User::create(['eid' => $request->input('eid'), 'password' => bcrypt($request->input('password'))]);
        // 邮件提醒
        $this->userData = User::where('eid', $request->input('eid'))->first();
        Mail::send('emails.register', ['username' => $this->userData->employee->name], function($message) {
            $message->from('hziflytek@126.com','移动互联签到系统');
            $message->to($this->userData->employee->email, $this->userData->employee->name);
            $message->subject('【注册提醒】');
        });
        return redirect('/')->with('success', 'success');
    }

    /**
     * 登出
     *
     * @return \Illuminate\Http\Response
     */
    public function logout()
    {
        Auth::logout();
        return redirect()->back();
    }

    protected function validatorLogin(array $data)
    {
        return Validator::make($data, [
            'eid' => 'required|max:255',
            'password' => 'required'
        ]);
    }

    protected function validatorRegister(array $data)
    {
        return Validator::make($data, [
            'eid' => 'required|max:255|exists:employees|unique:users',
            'password' => 'required|confirmed|min:8'
        ]);
    }
}