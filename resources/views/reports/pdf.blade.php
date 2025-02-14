<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Reporte Trabajadores</title>
    <style>
        div {
            display: block;
            float: left;
            width: 100%;
            font-size: 12px;
        }
    </style>
</head>
<body>
<div style="width: 100%; text-align: left; margin-top: 2cm; border: 2px solid grey;">
    <div style="width: 40%; padding-left: 15px; border-right: 3px solid grey">
        <div style="font-weight:bolder; font-size: 16px;">{{$company->names}}</div>
        <div style="padding-bottom: 10px; font-size: 12px;">{{$company->address}}</div>
    </div>

    <div style="width: 20%; padding-left: 15px; border-right: 3px solid grey">
        <div style="font-weight:bolder; font-size: 16px;">Teléfon</div>
        <div style="padding-bottom: 10px; font-size: 12px;">{{$company->phone}}</div>
    </div>
    <div style="width: 20%; padding-left: 15px;">
        <div style="font-weight:bolder; font-size: 16px;">Email</div>
        <div style="padding-bottom: 10px; font-size: 12px;">{{$company->email}}</div>
    </div>
</div>

<div style="width: 100%; text-align: left; margin-top: 0.5cm; border: 2px solid grey;">
    <div style="width: 20%; padding-left: 15px; border-right: 3px solid grey">
        <div style="font-weight:bolder; font-size: 16px;">Sucursal</div>
        <div style="padding-bottom: 10px; font-size: 12px;">@if ($filters['division'] > 0)  {{ $filters['division']}} @else Todas @endif</div>
    </div>

    <div style="width: 20%; padding-left: 15px; border-right: 3px solid grey">
        <div style="font-weight:bolder; font-size: 16px;">Roles</div>
        <div style="padding-bottom: 10px; font-size: 12px;">@if ($filters['rol'] > 0)  {{ $filters['rol']}} @else Todos @endif</div>
    </div>
    <div style="width: 20%; padding-left: 15px; border-right: 3px solid grey">
        <div style="font-weight:bolder; font-size: 16px;">Persona</div>
        <div style="padding-bottom: 10px; font-size: 12px;">@if ($filters['person'] > 0)  {{ $filters['person']}} @else Todos @endif</div>
    </div>
    <div style="width: 28%; padding-left: 15px;">
        <div style="font-weight:bolder; font-size: 16px;">Rango fecha</div>
        <div style="padding-bottom: 10px; font-size: 12px;">{{ date('d/m/Y H:i:s', strtotime($filters['dstar']))}} a {{ date('d/m/Y H:i:s', strtotime($filters['dend']))}} </div>
    </div>
</div>

<div style="width: 100%; text-align: left; margin-top: 25px; border: 2px solid grey;">
    <table style="width: 100%">
        <thead>
        <tr style="font-weight: bold; background-color: rgba(201,201,201,0.28); font-size: 11px;">
            <th style="padding: 10px 10px 10px 10px;">Sucursal</th>
            <th style="padding: 10px 10px 10px 10px;">Rol</th>
            <th style="padding: 10px 10px 10px 10px; text-align: right">Codigo</th>
            <th style="padding: 10px 10px 10px 10px; text-align: right">Nombre</th>
            <th style="padding: 10px 10px 10px 10px; text-align: right">Entrada</th>
            <th style="padding: 10px 10px 10px 10px; text-align: right">Salida</th>
            <th style="padding: 10px 10px 10px 10px; text-align: right">Horas</th>
        </tr>
        </thead>
        <tbody>
        @foreach($list as $ls)
            <tr style="font-size: 10px;">
                <td style="padding: 5px 5px 5px 10px; border-bottom: 1px solid rgba(169,169,169,0.29)">{{$ls['div']}}</td>
                <td style="padding: 5px 5px 5px 10px; border-bottom: 1px solid rgba(169,169,169,0.29)">{{$ls['rol']}}</td>
                <td style="padding: 5px 5px 5px 10px; border-bottom: 1px solid rgba(169,169,169,0.29);text-align: right">{{$ls['token']}}</td>
                <td style="padding: 5px 5px 5px 10px; border-bottom: 1px solid rgba(169,169,169,0.29);text-align: right">{{$ls['names']}}</td>
                <td style="padding: 5px 5px 5px 10px; border-bottom: 1px solid rgba(169,169,169,0.29);text-align: right">{{$ls['dstar']}}</td>
                <td style="padding: 5px 5px 5px 10px; border-bottom: 1px solid rgba(169,169,169,0.29);text-align: right">{{$ls['dend']}}</td>
                <td style="padding: 5px 5px 5px 10px; border-bottom: 1px solid rgba(169,169,169,0.29);text-align: right">{{$ls['hours']}}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

</body>
</html>
