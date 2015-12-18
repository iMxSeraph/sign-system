<!DOCTYPE html>
<html lang="zh-CN">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>杭州移动互联-仪表盘</title>

    <link rel="stylesheet" href="//cdn.bootcss.com/bootstrap/3.3.5/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">

    <script src="//cdn.bootcss.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="//cdn.bootcss.com/respond.js/1.4.2/respond.min.js"></script>
  </head>
  <body>
    <div class="container">
      <div class="jumbotron">
        <h3>基本信息</h3>
        <p>
          <table class="table">
            <thead>
              <tr class="active">
                <th>姓名</th>
                <th>工号</th>
                <th>工作邮箱</th>
              </tr>
            </thead>
            <tbody>
              <tr class="warning">
                <th>{{ $user->employee->name }}</th>
                <td>{{ $user->eid }}</td>
                <td>{{ $user->employee->email }}</td>
              </tr>
            </tbody>
          </table>
        </p>
        <p><a class="btn btn-danger" href="logout" role="button">退出登录</a>
        @if ($user->employee->admin) 
        <a class="btn btn-warning" href="admin" role="button">审核管理</a>
        <button type="button" class="btn btn-success" data-toggle="modal" data-target="#exportModal">数据导出</button>
        <button type="button" class="btn btn-info" data-toggle="modal" data-target="#workdayModal">特殊规则</button>
        @endif
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#systemModal">系统说明</button>
        <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#businessModal">其它申辩</button>
        <button type="button" class="btn btn-success" data-toggle="modal" data-target="#resultModal">数据统计</button>
        </p>
      </div>
      @if (!$errors->isEmpty())
        <div class="alert alert-danger" role="alert">
          <strong>发生错误</strong> {{ $errors->first() }}
        </div>
      @endif
      @if (session('success'))
        <div class="alert alert-success" role="alert">
          <strong>请求已完成</strong> {{ session('success') }}
        </div>
      @endif
      @if ($isLate)
      <div class="panel panel-danger">
        <div class="panel-heading">
          <h3 class="panel-title">迟到提醒</h3>
        </div>
        <div class="panel-body">
          你今天的签到时间为 {{ $firstSignIn or '无' }} ，迟到了 {{ $latetime }} 分钟。
          @if ($isComplained)
          你已经提交了申辩，请等待管理员审核。
          @endif
          @if (!$isComplained)
          <button type="button" class="btn btn-danger btn-xs" data-toggle="modal" data-target="#complainModal">迟到申辩</button>
          @endif
        </div>
      </div>
      @endif

      <div class="panel panel-primary">
        <div class="panel-heading">
          <h3 class="panel-title">今日概况</h3>
        </div>
        <div class="panel-body">
          <table class="table table-condensed table-striped">
            <thead>
              <tr>
                <th>日期</th>
                <th>类型</th>
                <th>签到时间</th>
                <th>签退时间</th>
                <th>加班时长</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <th>{{ date('Y年m月d日') }}</th>
                <th>{{ $isWorkday ? '工作日' : '休息日'}}
                <td>{{ $firstSignIn or '无' }}</td>
                <td>{{ $lastSignIn or '无' }}</td>
                <td>{{ $overtime }} 分钟</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="panel panel-info">
        <div class="panel-heading">
          <h3 class="panel-title">我的申辩</h3>
        </div>
        <div class="panel-body">
          <table class="table table-condensed table-striped">
            <thead>
              <tr>
                <th>开始日期</th>
                <th>结束日期</th>
                <th>类型</th>
                <th>状态</th>
                <th>审批人</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($complains as $complain)
                <tr class="{{ $complain->state == 'confirmed' ? 'success' : ($complain->state == 'rejected' ? 'danger': 'warning')}}">
                  <td>{{ $complain->startdate }}</td>
                  <td>{{ $complain->enddate }}</td>
                  <td>{{ $complain->type == 'late' ? '迟到' : ($complain->type == 'business' ? '出差' : '请假') }}</td>
                  <td>{{ $complain->state == 'confirmed' ? '已审核' : ($complain->state == 'rejected' ? '不通过': '待审核')}}</td>
                  <td>{{ $complain->operator }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="modal fade" id="systemModal" tabindex="-1" role="dialog" aria-labelledby="systemModalLabel">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="systemModalLabel">系统说明</h4>
          </div>
          <div class="modal-body">
            <p class="text-danger">签到算法</p>
            <p>工作日07:00开始为有效签到时间，在07:00~09:30之间签到算作有效签到。</p>
            <p class="text-danger">迟到算法</p>
            <p>工作日09:30以前没有签到信息算作迟到，迟到时长为09:00至签到时间的间隔</p>
            <p>如果全天没有签到信息记为旷工。</p>
            <p class="text-danger">加班算法</p>
            <p>工作日08:50之前签到记为加班，加班时长为最早一次签到时间至09:00</p>
            <p>工作日18:40以后签到记为加班，加班时长为18:20至最后一次签到时间</p>
            <p>非工作日全天记为加班，加班时长为第一次签到和最后一次签到的区间</p>
            <p class="text-danger">申辩规则</p>
            <p>如忘记签到，当日内可以提出申辩，过期无法申辩，记为迟到或旷工。</p>
            <p>有特殊情况例如出差或请假可以特殊申辩。申辩统一由 @光宇 进行处理。</p>
            <p class="text-info">使用说明</p>
            <p>在仪表盘可以看到自己的所有申辩和迟到信息，申辩经审核以后会自动取消迟到状态</p>
            <p>如有发现任何BUG请联系 @牧心 进行处理</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-primary" data-dismiss="modal">知道了</button>
          </div>
        </div>
      </div>
    </div>

    @if (!$isComplained)
    <div class="modal fade" id="complainModal" tabindex="-1" role="dialog" aria-labelledby="complainModalLabel">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="complainModalLabel">迟到申辩</h4>
          </div>
          <form method="post" action="dashboard">
          <input type="hidden" name="type" value="complain">
            {!! csrf_field() !!}
            <div class="modal-body">
              <div class="form-group">
                <textarea class="form-control" name="description" rows="3" placeholder="请输入申诉理由（至少5个字）">{{old('description')}}</textarea>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
              <button type="submit" class="btn btn-primary">提交</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    @endif

    @if ($user->employee->admin) 
    <div class="modal fade" id="workdayModal" tabindex="-1" role="dialog" aria-labelledby="workdayModalLabel">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="workdayModalLabel">特殊日期添加</h4>
          </div>
          <form method="post" action="dashboard">
            {!! csrf_field() !!}
            <input type="hidden" name="type" value="workday">
            <div class="modal-body">
              <div class="form-group">
                暂时没有删除和维护特殊日期列表的功能，请慎重添加。
              </div>
              <div class="form-group">
                <input type="input" class="form-control" name="startdate" placeholder="开始日期（例如：2015-09-01）" value="{{old('startdate')}}">
              </div>
              <div class="form-group">
                <input type="input" class="form-control" name="enddate" placeholder="结束日期（例如：2015-09-02）" value="{{old('enddate')}}">
              </div>
              <div class="radio">
                <label>
                  <input type="radio" name="isworkday" value="1" checked>
                  工作日
                </label>
              </div>
              <div class="radio">
                <label>
                  <input type="radio" name="isworkday" value="0">
                  休息日
                </label>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
              <button type="submit" class="btn btn-primary">添加规则</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <div class="modal fade" id="exportModal" tabindex="-1" role="dialog" aria-labelledby="exportModalLabel">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="exportModalLabel">签到数据导出</h4>
          </div>
          <form method="post" action="dashboard">
            {!! csrf_field() !!}
            <input type="hidden" name="type" value="export">
            <div class="modal-body">
              <div class="form-group">
                请输入开始查询和结束查询的日期，请注意格式规范。(较为耗时请耐心等待)
              </div>
              <div class="form-group">
                <input type="date" class="form-control" name="startdate" placeholder="开始日期（例如：2015-09-01）" value="{{old('startdate')}}">
              </div>
              <div class="form-group">
                <input type="date" class="form-control" name="enddate" placeholder="结束日期（例如：2015-09-02）" value="{{old('enddate')}}">
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
              <button type="submit" class="btn btn-primary">导出</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    @endif

    <div class="modal fade" id="resultModal" tabindex="-1" role="dialog" aria-labelledby="resultModalLabel">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="resultModalLabel">个人数据查询</h4>
          </div>
          <form method="post" action="dashboard">
            {!! csrf_field() !!}
            <input type="hidden" name="type" value="result">
            <div class="modal-body">
              <div class="form-group">
                请输入开始查询和结束查询的日期，请注意格式规范。
              </div>
              <div class="form-group">
                <input type="date" class="form-control" name="startdate" placeholder="开始日期（例如：2015-09-01）" value="{{old('startdate')}}">
              </div>
              <div class="form-group">
                <input type="date" class="form-control" name="enddate" placeholder="结束日期（例如：2015-09-02）" value="{{old('enddate')}}">
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
              <button type="submit" class="btn btn-primary">查询</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <div class="modal fade" id="businessModal" tabindex="-1" role="dialog" aria-labelledby="businessModalLabel">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="businessModalLabel">其它申辩</h4>
          </div>
          <form method="post" action="dashboard">
          <input type="hidden" name="type" value="business">
            {!! csrf_field() !!}
            <div class="modal-body">
              <div class="form-group">
                请输入开始和结束日期，请注意格式规范。
              </div>
              <div class="form-group">
                <input type="date" class="form-control" name="startdate" placeholder="开始日期（例如：2015-09-01）" value="{{old('startdate')}}">
              </div>
              <div class="form-group">
                <input type="date" class="form-control" name="enddate" placeholder="结束日期（例如：2015-09-02）" value="{{old('enddate')}}">
              </div>
              <div class="form-group">
                <textarea class="form-control" name="description" rows="3" placeholder="请输入申诉理由（至少5个字）">{{old('description')}}</textarea>
              </div>
              <div class="radio">
                <label>
                  <input type="radio" name="businessType" value="business" checked>
                  出差
                </label>
              </div>
              <div class="radio">
                <label>
                  <input type="radio" name="businessType" value="ask">
                  请假
                </label>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
              <button type="submit" class="btn btn-primary">提交</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <script src="//cdn.bootcss.com/jquery/1.11.3/jquery.min.js"></script>
    <script src="//cdn.bootcss.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
  </body>
</html>
