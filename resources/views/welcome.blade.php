<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">

        <!-- Styles -->

        <style>
            body {
                font-family: 'Nunito';
            }
        </style>
    </head>
    <body class="antialiased">
    <div>
        上传文件 ： <input type="file" name = "file" id = "fileId" />

        <button  type = "submit" name = "btn" value = "提交" id = "btnId" onclick="check()" /> 提交
    </div>

    <script>

        function check() {

            var objFile = document.getElementById("fileId");
            if(objFile.value == "") {
                alert("不能空")
            }

            console.log(objFile.files[0].size); // 文件字节数

            var files = $('#fileId').prop('files');//获取到文件列表
            if(files.length == 0){
                alert('请选择文件');
            }else{
                var reader = new FileReader();//新建一个FileReader
                reader.readAsText(files[0], "UTF-8");//读取文件
                reader.onload = function(evt){ //读取完文件之后会回来这里
                    var fileString = evt.target.result; // 读取文件内容
                }
            }

        }
    </body>
</html>
