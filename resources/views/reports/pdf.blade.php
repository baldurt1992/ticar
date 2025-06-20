@php
    $columns_originales = $columns_originales ?? $columns;
    $columnMap = [
        'division' => 'div',
        'role' => 'rol',
        'name' => 'names',
        'token' => 'token',
        'moment_enter' => 'moment_enter',
        'moment_exit' => 'moment_exit',
        'hours' => 'hours',
        'is_otros' => 'is_otros',
        'note' => 'note',
        'motive_name' => 'motive_name',
    ];
    $columns = collect($columns)->map(fn($c) => $columnMap[$c] ?? $c)->toArray();
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
        @php
            $registros = collect($registros);
            $normales = !$is_solo_otros ? $registros->filter(fn($r) => ($r['motive_id'] ?? 0) == 0) : collect();
            $otros = $registros->filter(fn($r) => ($r['motive_id'] ?? 0) > 0);
        @endphp

        @if(!$is_solo_otros && $normales->count())
            <table style="width: 100%; margin-top: 25px; margin-bottom: 5px;">
                <tr style="font-size: 13px; font-weight: bold;">
                    <td><strong>ENTRADA NORMALES — Usuario: {{ $token }}</strong></td>
                    <td style="text-align: right;"><strong>Total horas acumuladas:</strong></td>
                    <td style="text-align: left;"><strong>{{ $totales[$token] ?? '00:00:00' }}</strong></td>
                </tr>
            </table>

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
                    @foreach($normales as $ls)
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
                            </td> @endif
                            @if(in_array('moment_exit', $columns))
                                <td style="text-align: center;">
                                    {{ !empty($ls['moment_exit']) ? \Carbon\Carbon::parse($ls['moment_exit'])->format('d/m/Y H:i') : '-' }}
                            </td> @endif
                            @if(in_array('hours', $columns))
                            <td style="text-align: center;">{{ $ls['hours'] ?? '0:00' }}</td> @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        @if(in_array('is_otros', $columns_originales) && $otros->count())
            <table style="width: 100%; margin-top: 25px; margin-bottom: 5px;">
                <tr style="color: red; font-size: 13px; font-weight: bold;">
                    <td><strong>ENTRADAS CON MOTIVO</strong></td>
                    <td style="text-align: right;"><strong>Total horas acumuladas:</strong></td>
                    <td style="text-align: left;">
                        <strong>{{ $totales_otros[$token] ?? '00:00:00' }}</strong>
                    </td>
                </tr>
            </table>

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
                        @if(in_array('motive_name', $columns))
                        <th>Motivo</th> @endif
                        @if(in_array('note', $columns))
                        <th>Nota</th> @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($otros as $ls)
                        <tr style="color: red;">
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
                            <td style="text-align: center;">{{ $ls['hours'] ?? '0:00' }}</td> @endif
                            @if(in_array('motive_name', $columns))
                            <td>{{ $ls['motive_name'] ?? '-' }}</td> @endif
                            @if(in_array('note', $columns))
                            <td>{{ $ls['note'] ?? '' }}</td> @endif
                        </tr>
                    @endforeach
                </tbody>

            </table>
        @endif
    @endforeach
</body>

</html>