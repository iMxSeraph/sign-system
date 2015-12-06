<!DOCTYPE html>
<html lang="zh-CN">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>杭州移动互联-登录</title>

    <link rel="stylesheet" href="//cdn.bootcss.com/bootstrap/3.3.5/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/homepage.css">

    <script src="//cdn.bootcss.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="//cdn.bootcss.com/respond.js/1.4.2/respond.min.js"></script>
  </head>
  <body>
    <div class="container">
      <form class="form-signin" method="post" action="/">
        {!! csrf_field() !!}
        <h2 class="form-signin-heading">杭州移动互联</h2>
        @if (session('success'))
        <div class="alert alert-success" role="alert">
          <strong>注册成功</strong> 请登录
        </div>
        @endif
        @if (!$errors->isEmpty())
        <div class="alert alert-danger" role="alert">
          <strong>登录失败</strong> {{$errors->first()}}
        </div>
        @endif
        <label for="inputEid" class="sr-only">工号</label>
        <input type="text" name='eid' id="inputEid" class="form-control" placeholder="工号" required="" autofocus="" value="{{old('eid')}}">
        <label for="inputPassword" class="sr-only">密码</label>
        <input type="password" name='password' id="inputPassword" class="form-control" placeholder="密码" required="">
        <div class="checkbox">
          <label>
            <input type="checkbox" name="remember" value="true"> 保存登录状态
          </label>
        </div>
        <button class="btn btn-lg btn-primary" type="submit">登录</button>
        <a class="btn btn-lg btn-info" href="register" role="button">注册</a>
      </form>
    </div>
    <script src="//cdn.bootcss.com/jquery/1.11.3/jquery.min.js"></script>
    <script src="//cdn.bootcss.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
  </body>
</html>