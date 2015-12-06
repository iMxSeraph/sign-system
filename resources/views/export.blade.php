<!DOCTYPE html>
<html lang="zh-CN">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>杭州移动互联-数据导出</title>

    <link rel="stylesheet" href="//cdn.bootcss.com/bootstrap/3.3.5/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">

    <script src="//cdn.bootcss.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="//cdn.bootcss.com/respond.js/1.4.2/respond.min.js"></script>
  </head>
  <body>
    <div class="container">
      <div class="jumbotron">
        <h3>签到数据导出</h3>
      </div>
      <div class="panel panel-info">
        <div class="panel-heading">
          <h3 class="panel-title">加班情况</h3>
        </div>
        <div class="panel-body">
          <table class="table table-condensed table-striped">
            <thead>
              <tr>
                <th>序号</th>
                <th>类型</th>
                <th>工号</th>
                <th>姓名</th>
                <th>日期</th>
                <th>加班时间</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($overResults as $result)
              <tr>
                <td>{{ $result['id'] }}</td>
                <td>加班</td>
                <td>{{ $result['eid'] }}</td>
                <td>{{ $result['name'] }}</td>
                <td>{{ $result['date'] }}</td>
                <td>{{ $result['overtime'] }}</td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>

      <div class="panel panel-danger">
        <div class="panel-heading">
          <h3 class="panel-title">迟到情况</h3>
        </div>
        <div class="panel-body">
          <table class="table table-condensed table-striped">
            <thead>
              <tr>
                <th>序号</th>
                <th>类型</th>
                <th>工号</th>
                <th>姓名</th>
                <th>日期</th>
                <th>迟到时间</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($lateResults as $result)
              <tr>
                <td>{{ $result['id'] }}</td>
                <td>{{ $result['latetime'] == 540 ? '旷工' : '迟到' }}</td>
                <td>{{ $result['eid'] }}</td>
                <td>{{ $result['name'] }}</td>
                <td>{{ $result['date'] }}</td>
                <td>{{ $result['latetime'] }}</td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>

      <div class="panel panel-warning">
        <div class="panel-heading">
          <h3 class="panel-title">晚到情况</h3>
        </div>
        <div class="panel-body">
          <table class="table table-condensed table-striped">
            <thead>
              <tr>
                <th>序号</th>
                <th>类型</th>
                <th>工号</th>
                <th>姓名</th>
                <th>日期</th>
                <th>晚到时间</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($lateResults as $result)
              <tr>
                <td>{{ $result['id'] }}</td>
                <td>晚到</td>
                <td>{{ $result['eid'] }}</td>
                <td>{{ $result['name'] }}</td>
                <td>{{ $result['date'] }}</td>
                <td>{{ $result['latetime'] }}</td>
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
