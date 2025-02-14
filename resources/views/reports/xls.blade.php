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
