<!doctype html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte Trabajadores</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 11px;
            margin: 2cm 1cm 1cm 1cm;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        th,
        td {
            padding: 6px 8px;
            border-bottom: 1px solid #ccc;
            text-align: left;
        }

        thead {
            background-color: #f0f0f0;
            font-weight: bold;
        }

        .totales {
            font-weight: bold;
            background-color: #eee;
        }

        .encabezado {
            border: 1px solid #888;
            margin-bottom: 20px;
        }

        .encabezado div {
            display: inline-block;
            width: 32%;
            padding: 10px;
            vertical-align: top;
        }

        .encabezado .separado {
            border-left: 1px solid #ccc;
        }

        h3 {
            margin-bottom: 5px;
        }
    </style>
</head>

<body>

    <div class="encabezado">
        <div>
            <h3>{{ $company->names }}</h3>
            <p>{{ $company->address }}</p>
        </div>
        <div class="separado">
            <h3>Teléfono</h3>
            <p>{{ $company->phone }}</p>
        </div>
        <div class="separado">
            <h3>Email</h3>
            <p>{{ $company->email }}</p>
        </div>
    </div>

    <div class="encabezado">
        <div>
            <strong>Sucursal:</strong>
            {{ $filters['division'] > 0 ? $filters['division'] : 'Todas' }}
        </div>
        <div class="separado">
            <strong>Rol:</strong>
            {{ $filters['rol'] > 0 ? $filters['rol'] : 'Todos' }}
        </div>
        <div class="separado">
            <strong>Persona:</strong>
            {{ $filters['person'] > 0 ? $filters['person'] : 'Todas' }}
        </div>
        <div style="display:block; margin-top: 10px;">
            <strong>Rango Fecha:</strong>
            {{ date('d/m/Y H:i:s', strtotime($filters['dstar'])) }} a
            {{ date('d/m/Y H:i:s', strtotime($filters['dend'])) }}
        </div>
    </div>

    @foreach($agrupados as $token => $registros)
        <h3 style="margin-top: 25px;">Usuario: {{ $token }}</h3>
        <table>
            <thead>
                <tr>
                    <th>Sucursal</th>
                    <th>Rol</th>
                    <th>Código</th>
                    <th>Nombre</th>
                    <th>Entrada</th>
                    <th>Salida</th>
                    <th>Horas</th>
                </tr>
            </thead>
            <tbody>
                @foreach($registros as $item)
                    <tr>
                        <td>{{ $item['div'] }}</td>
                        <td>{{ $item['rol'] }}</td>
                        <td>{{ $item['token'] }}</td>
                        <td>{{ $item['names'] }}</td>
                        <td>
                            {{ $item['moment_enter'] ? \Carbon\Carbon::parse($item['moment_enter'])->format('d/m/y H:i') : '-' }}
                        </td>
                        <td>
                            {{ $item['moment_exit'] ? \Carbon\Carbon::parse($item['moment_exit'])->format('d/m/y H:i') : '-' }}
                        </td>
                        <td>
                            {{ $item['hours'] ?? '0:00' }}
                        </td>
                    </tr>
                @endforeach
                <tr class="totales">
                    <td colspan="6" style="text-align: right;">Total horas acumuladas:</td>
                    <td>{{ $totales[$token] ?? '0:00' }}</td>
                </tr>
            </tbody>
        </table>
    @endforeach

</body>

</html>