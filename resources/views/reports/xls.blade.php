@php
    $originalList = collect($list);

    $list = $originalList->filter(fn($ls) => ($ls['motive_id'] ?? 0) == 0)->map(function ($ls) {
        return array_merge([
            'div' => $ls['div'] ?? '',
            'rol' => $ls['rol'] ?? '',
            'names' => $ls['names'] ?? '',
            'token' => $ls['token'] ?? '',
            'moment_enter' => $ls['moment_enter'] ?? null,
            'moment_exit' => $ls['moment_exit'] ?? null,
            'hours' => $ls['hours'] ?? '0:00',
            'note' => $ls['note'] ?? '',
            'motive_id' => $ls['motive_id'] ?? 0,
            'motive_name' => $ls['motive_name'] ?? '',
        ]);
    });

    $totalOtros = $originalList->reduce(function ($carry, $r) {
        if (($r['motive_id'] ?? 0) > 0 && !empty($r['moment_enter']) && !empty($r['moment_exit'])) {
            $start = \Carbon\Carbon::parse($r['moment_enter']);
            $end = \Carbon\Carbon::parse($r['moment_exit']);
            return $carry + $start->diffInSeconds($end);
        }
        return $carry;
    }, 0);

    $totalOtrosFormatted = sprintf('%02d:%02d:%02d', floor($totalOtros / 3600), floor(($totalOtros % 3600) / 60), $totalOtros % 60);

    $otros = $originalList->filter(fn($ls) => ($ls['motive_id'] ?? 0) > 0)->map(function ($ls) {
        return array_merge([
            'div' => $ls['div'] ?? '',
            'rol' => $ls['rol'] ?? '',
            'names' => $ls['names'] ?? '',
            'token' => $ls['token'] ?? '',
            'moment_enter' => $ls['moment_enter'] ?? null,
            'moment_exit' => $ls['moment_exit'] ?? null,
            'hours' => $ls['hours'] ?? '0:00',
            'note' => $ls['note'] ?? '',
            'motive_id' => $ls['motive_id'] ?? 0,
            'motive_name' => $ls['motive_name'] ?? '',
        ]);
    });

    $formatHora = fn($moment) => $moment ? \Carbon\Carbon::parse($moment)->format('d/m/y H:i') : '-';
@endphp

<table style="width: 100%; margin-bottom: 5px;">
    <tr style="font-size: 13px; font-weight: bold;">
        <td><strong>ENTRADAS NORMALES</strong></td>
        <td style="text-align: right;"><strong>Total horas acumuladas:</strong></td>
        <td style="text-align: left;"><strong>{{ $total_horas }}</strong></td>
    </tr>
</table>

<table style="width: 100%; border-collapse: collapse;">
    <thead>
        <tr style="background-color: #e3e3e3; font-size: 11px;">
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
        @foreach($list as $ls)
            <tr style="font-size: 10px;">
                @if(in_array('div', $columns))
                <td>{{ $ls['div'] }}</td> @endif
                @if(in_array('rol', $columns))
                <td>{{ $ls['rol'] }}</td> @endif
                @if(in_array('token', $columns))
                <td style="text-align: right">{{ $ls['token'] }}</td> @endif
                @if(in_array('names', $columns))
                <td>{{ $ls['names'] }}</td> @endif
                @if(in_array('moment_enter', $columns))
                <td>{{ $formatHora($ls['moment_enter']) }}</td> @endif
                @if(in_array('moment_exit', $columns))
                <td>{{ $formatHora($ls['moment_exit']) }}</td> @endif
                @if(in_array('hours', $columns))
                <td style="text-align: right">{{ $ls['hours'] }}</td> @endif
            </tr>
        @endforeach
    </tbody>
</table>

@if($otros->count())
    <table style="width: 100%; margin-top: 20px; margin-bottom: 5px;">
        <tr style="color: red; font-size: 13px; font-weight: bold;">
            <td><strong>ENTRADAS CON MOTIVO</strong></td>
            <td style="text-align: right;"><strong>Total horas acumuladas:</strong></td>
            <td style="text-align: left;"><strong>{{ $totalOtrosFormatted }}</strong></td>
        </tr>
    </table>

    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="background-color: #fce4e4; font-size: 11px;">
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
                <tr style="font-size: 10px; color: red;">
                    @if(in_array('div', $columns))
                    <td>{{ $ls['div'] }}</td> @endif
                    @if(in_array('rol', $columns))
                    <td>{{ $ls['rol'] }}</td> @endif
                    @if(in_array('token', $columns))
                    <td style="text-align: right">{{ $ls['token'] }}</td> @endif
                    @if(in_array('names', $columns))
                    <td>{{ $ls['names'] }}</td> @endif
                    @if(in_array('moment_enter', $columns))
                    <td>{{ $formatHora($ls['moment_enter']) }}</td> @endif
                    @if(in_array('moment_exit', $columns))
                    <td>{{ $formatHora($ls['moment_exit']) }}</td> @endif
                    @if(in_array('hours', $columns))
                    <td style="text-align: right">{{ $ls['hours'] }}</td> @endif
                    @if(in_array('motive_name', $columns))
                    <td>{{ $ls['motive_name'] ?? '-' }}</td> @endif
                    @if(in_array('note', $columns))
                    <td>{{ $ls['note'] ?? '' }}</td> @endif
                </tr>
            @endforeach
        </tbody>
    </table>
@endif