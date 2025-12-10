<!DOCTYPE html>
<html>
<head>
    <title>PRUEBA FACTURA</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" >
</head>
<body>
    <!-- ESTILOS DEL RIDE -->
    <style>
        *{
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
        }

        body{
            background-image: url('public/bg_wpp.jpeg');
        }

        h3{
            margin: 0;
            font-size: 14px;
        }

        .fondo{
            width: 120%;
            height: calc(100vh - 100px);
            position: fixed;
            top: -50px;
            left: -50px;
            z-index: 20;
        }

        .Ride__head{
            width: 120%;
            position: fixed;
            z-index: 30;
            top: -50px;
            left: -50px;
            background-color: white;
            height: 60px;
            padding: 5px 0;
        }

        .Chats__items{
            width: 100%;
            position: fixed;
            z-index: 40;
            top: 60px;
            left: 0;
            right: 0;
        }

        .Chat__item{
            width: 50%;
        }

        .Chat__item>td{
            padding: 10px 10px;
            border-radius: 5px;
            font-size: 12px;
        }

        .Chat__item>td>label{
            display: inline-block;
            width: 50%;
        }

    </style>

    <img class="fondo" src="{{ 'data:image/png'. ';base64,' . base64_encode(file_get_contents('http://193.46.198.228:8085/back/public/bg_wp.png')) }}">

    <!-- 1) Encabezado del chat -->
    <table class="Ride__head">
        <tr>
            <td style="width:10%;">
                <div
                    style="width: 60px;height:60px;border-radius:50%;background-color:#F6F6F6;margin-left:50px;"
                ></div>
            </td>
            <td style="width:90%;">
                <div class="Ride__datesDoc">
                    <h3 style="margin-left:20px;">{{ $client_name }}</h3>
                    <h3 style="margin-left:20px;">+{{ $client_phone }}</h3>
                </div>
            </td>
        </tr>
    </table>

    <!-- 2) mensajes -->
    <table class="Chats__items">
        @foreach (json_decode($items) as $item)
            <tr class="Chat__item">
                @if ($item->from_me)
                    <td style="background-color: #D9FDD3;text-align:right; width:200px;">
                        <label>{{ $item->text }}</label>
                        <label>{{ date('H:i:s',$item->timestamp) }}</label>
                    </td>
                @else
                    <td style="background-color: white;text-align:left;width:200px;">
                        <label>{{ $item->text }}</label>
                        <label>{{ date('H:i:s',$item->timestamp) }}</label>
                    </td>
                @endif
            </tr>
        @endforeach
    </table>
    
</body>
</html>