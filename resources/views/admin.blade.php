<!DOCTYPE html>
<html lang="zh-CN">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>杭州移动互联-管理审核</title>

    <link rel="stylesheet" href="//cdn.bootcss.com/bootstrap/3.3.5/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">

    <script src="//cdn.bootcss.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="//cdn.bootcss.com/respond.js/1.4.2/respond.min.js"></script>
  </head>
  <body>
    <div class="container">
      <div class="jumbotron">
        <h3>审核管理</h3>
      </div>
      @if (session('success'))
        <div class="alert alert-success" role="alert">
          <strong>请求已完成</strong> {{ session('success') }}
        </div>
      @endif
      <div class="panel panel-info">
        <div class="panel-heading">
          <h3 class="panel-title">待申辩项</h3>
        </div>
        <div class="panel-body">
          <table class="table table-condensed table-striped">
            <thead>
              <tr>
                <th>类型</th>
                <th>开始日期</th>
                <th>结束日期</th>
                <th>申请人</th>
                <th>描述</th>
                <th>审核</th>
              </tr>
            </thead>
            <tbody>
            @foreach ($complains as $complain)
              <tr class="{{ $complain->state == 'confirmed' ? 'success' : ($complain->state == 'rejected' ? 'danger': 'warning')}}">
                <td>{{ $complain->type == 'late' ? '迟到' : ($complain->type == 'business' ? '出差' : '请假') }}</td>
                <td>{{ $complain->startdate }}</td>
                <td>{{ $complain->enddate }}</td>
                <td>{{ $complain->employee->name }}</td>
                <td>{{ $complain->description }}</td>
                <td>
                  @if ($complain->state == '')
                  <a class="btn btn-success btn-xs" href="confirm/{{ $complain->token }}" role="button">通过</a>
                  <a class="btn btn-danger btn-xs" href="confirm/{{ $complain->token }}?reject=1" role="button">不通过</a>
                  @elseif ($complain->state == 'confirmed')
                  {{ $complain->operator }} 已审核
                  @else
                  {{ $complain->operator }} 已拒绝
                  @endif
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <script src="//cdn.bootcss.com/jquery/1.11.3/jquery.min.js"></script>
    <script src="//cdn.bootcss.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
  </body>
</html>
