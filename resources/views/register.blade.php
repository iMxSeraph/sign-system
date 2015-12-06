<!DOCTYPE html>
<html lang="zh-CN">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>杭州移动互联-注册</title>

    <link rel="stylesheet" href="//cdn.bootcss.com/bootstrap/3.3.5/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/homepage.css">

    <script src="//cdn.bootcss.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="//cdn.bootcss.com/respond.js/1.4.2/respond.min.js"></script>
  </head>
  <body>
    <div class="container">
      <form class="form-signin" method="post" action="register">
        {!! csrf_field() !!}
        <h2 class="form-signin-heading">新用户注册</h2>
        @if (!$errors->isEmpty())
        <div class="alert alert-danger" role="alert">
          <strong>注册失败</strong> {{$errors->first()}}
        </div>
        @endif
        <input type="text" name='eid' id="inputEid" class="form-control" placeholder="工号" required="" autofocus="" value="{{old('eid')}}">
        <input type="password" name="password" id="inputPassword" class="form-control" placeholder="密码（至少8位）" required="">
        <input type="password" name="password_confirmation" id="inputPasswordConfirm" class="form-control" placeholder="确认密码" required="">
        <button class="btn btn-lg btn-primary" type="submit">注册</button>
        <button class="btn btn-lg btn-warning" type="button">忘记密码</button>
      </form>
    </div>
    <script src="//cdn.bootcss.com/jquery/1.11.3/jquery.min.js"></script>
    <script src="//cdn.bootcss.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
  </body>
</html>