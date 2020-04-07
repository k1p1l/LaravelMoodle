<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css"
          integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <title>Compilation</title>
</head>
<body>
<form method="post" action="{{url('/resource/compil')}}" enctype="multipart/form-data">
    @csrf
    <div class="form-group">
        <label for="exampleFormControlTextarea1">Write code</label>
        <textarea name="text_code" class="form-control" id="exampleFormControlTextarea1" rows="15"></textarea>
    </div>
    <div class="form-group">
        <label for="exampleFormControlFile1">File with code</label>
        <input name="file_code" type="file" class="form-control-file" id="exampleFormControlFile1">
    </div>
    <div class="form-group text-center">
        <input type="submit" name="add" class="btn btn-success" value="Add">
    </div>
</form>
</body>
</html>
