<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
  
    <table style="width:600px;max-width:100%;">
        <thead>
            <tr style="height:110px;background-color:#e7e7e7;">
                <th>
                    <img src="https://www.bixbytessolutions.com/assets/images/BixBytes_logo.png" alt="Bix Bytes Solutions" style="height: 70px;text-align: left;">
                </th>
            </tr>
        </thead>
        <tbody>
            <tr style="height: 250px;">
                <td>
                    <h4>Hi {{$username}},</h4>
                    <p>{{ $content }}</p>
                    <br>
                    <a href="{{ $link }}">
                    <button style="background-color:#e97f26;color:#fff;padding-top:10px;padding-bottom:10px;padding-right:25px;padding-left:25px;text-decoration: none;">Open Link</button>
                    </a>
                </td>
            </tr>
        </tbody>
        <tfoot>
            <tr style="height: 45px;background: rgb(82, 80, 80);text-align: center;">
                <td>
                    <a href="https://www.bixbytessolutions.com/" style="color:#fff;text-decoration:none;">Copyright © 2019 BixBytes Solutions </a>
                </td>
            </tr>
        </tfoot>
    </table>
</body>
</html>