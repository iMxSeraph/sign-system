<!DOCTYPE html>
<html lang="zh-CN">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>杭州移动互联-数据查询</title>

    <link rel="stylesheet" href="//cdn.bootcss.com/bootstrap/3.3.5/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">

    <script src="//cdn.bootcss.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="//cdn.bootcss.com/respond.js/1.4.2/respond.min.js"></script>
  </head>
  <body>
    <div class="container">
      <div class="jumbotron">
        <h3>个人数据查询</h3>
      </div>
      <div class="panel panel-info">
        <div class="panel-heading">
          <h3 class="panel-title">签到情况</h3>
        </div>
        <div class="panel-body">
          <table class="table table-condensed table-striped">
            <thead>
              <tr>
                <th>日期</th>
                <th>类型</th>
                <th>签到时间</th>
                <th>签退时间</th>
                <th>加班时间</th>
                <th>迟到时间</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($results as $result)
              <tr class="{{ $result['type'] == false ? 'success' : ($result['latetime'] > 0 ? 'danger': '') }}">
                <td>{{ $result['date'] }}</td>
                <td>{{ $result['type'] ? '工作日' : '休息日'}}</td>
                <td>{{ $result['firstSignIn'] }}</td>
                <td>{{ $result['lastSignIn'] }}</td>
                <td>{{ $result['overtime'] > 0 ? $result['overtime'] : '—' }}</td>
                <td>
				  @if ($result['latetime'] == 0)
				  —
				  @elseif ($result['latetime'] == 540)
				  旷工
				  @elseif ($result['latetime'] < 0)
				  申辩通过
				  @else
				  {{ $result['latetime'] }}
				  @endif
              </tr>
              @endforeach
              <tr class="info">
                <td>总计</td>
                <td>—</td>
                <td>—</td>
                <td>—</td>
                <td>{{ $overtimeSum }}</td>
                <td>{{ $latetimeSum }} 次</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <script src="//cdn.bootcss.com/jquery/1.11.3/jquery.min.js"></script>
    <script src="//cdn.bootcss.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
  </body>
</html>
