@php
    $columns = $columns ?? ['div', 'rol', 'token', 'names', 'moment_enter', 'moment_exit', 'hours'];
@endphp

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
            {{ isset($filters['division']) && $filters['division'] > 0
    ? (\App\Division::find($filters['division'])->names ?? 'Sucursal desconocida')
    : 'Todas' }}
        </div>
        <div class="separado">
            <strong>Rol:</strong>
            {{ isset($filters['rol']) && $filters['rol'] > 0
    ? (\App\Rol::find($filters['rol'])->rol ?? 'Rol desconocido')
    : 'Todos' }}
        </div>
        <div class="separado">
            <strong>Persona:</strong>
            {{ isset($filters['person']) && $filters['person'] > 0
    ? (\App\Person::find($filters['person'])->names ?? 'Persona desconocida')
    : 'Todas' }}
        </div>
        <div style="display:block; margin-top: 10px;">
            <strong>Rango Fecha:</strong>
            {{ isset($filters['dstar']) ? date('d/m/Y H:i:s', strtotime($filters['dstar'])) : 'No definido' }}
            {{ isset($filters['dend']) ? date('d/m/Y H:i:s', strtotime($filters['dend'])) : 'No definido' }}
        </div>
    </div>

    @foreach($agrupados as $token => $registros)
        <h3 style="margin-top: 25px;">Usuario: {{ $token }}</h3>
        <table>
            <thead>
                <tr>
                    @if(in_array('div', $columns))
                    <th>Sucursal</th> @endif
                    @if(in_array('rol', $columns))
                    <th>Rol</th> @endif
                    @if(in_array('token', $columns))
                    <th>Código</th> @endif
                    @if(in_array('names', $columns))
                    <th>Nombre</th> @endif
                    @if(in_array('moment_enter', $columns))
                    <th>Entrada</th> @endif
                    @if(in_array('moment_exit', $columns))
                    <th>Salida</th> @endif
                    @if(in_array('hours', $columns))
                    <th>Horas</th> @endif
                </tr>
            </thead>
            <tbody>
                @foreach($registros as $ls)
                    <tr>
                        @if(in_array('div', $columns))
                        <td>{{ $ls['div'] ?? '' }}</td> @endif
                        @if(in_array('rol', $columns))
                        <td>{{ $ls['rol'] ?? '' }}</td> @endif
                        @if(in_array('token', $columns))
                        <td style="text-align: right;">{{ $ls['token'] ?? '' }}</td> @endif
                        @if(in_array('names', $columns))
                        <td>{{ $ls['names'] ?? '' }}</td> @endif
                        @if(in_array('moment_enter', $columns))
                            <td style="text-align: center;">
                                {{ !empty($ls['moment_enter']) ? \Carbon\Carbon::parse($ls['moment_enter'])->format('d/m/Y H:i') : '-' }}
                            </td>
                        @endif
                        @if(in_array('moment_exit', $columns))
                            <td style="text-align: center;">
                                {{ !empty($ls['moment_exit']) ? \Carbon\Carbon::parse($ls['moment_exit'])->format('d/m/Y H:i') : '-' }}
                            </td>
                        @endif
                        @if(in_array('hours', $columns))
                            <td style="text-align: center;">{{ $ls['hours'] ?? '0:00' }}</td>
                        @endif
                    </tr>
                @endforeach

                @if(in_array('hours', $columns))
                    <tr class="totales">
                        <td colspan="{{ count($columns) - 1 }}" style="text-align: right;">Total horas acumuladas:</td>
                        <td style="text-align: center;">{{ $totales[$token] ?? '0:00' }}</td>
                    </tr>
                @endif
            </tbody>
        </table>
    @endforeach

</body>

</html>