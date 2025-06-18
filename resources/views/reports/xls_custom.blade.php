@php
    $list = collect($list)->map(function ($ls) {
        return [
            'division' => $ls['division'] ?? '',
            'role' => $ls['role'] ?? '',
            'token' => $ls['token'] ?? '',
            'name' => $ls['name'] ?? '',
            'moment_enter' => $ls['moment_enter'] ?? '',
            'moment_exit' => $ls['moment_exit'] ?? '',
            'hours' => $ls['hours'] ?? '0:00'
        ];
    })->all();
@endphp
<table style="width: 100%">
    {{-- Debug: ver qué datos llegan a la vista --}}
    {{-- @dd($list) --}}
    <thead>
        <tr style="font-weight: bold; background-color: rgba(201,201,201,0.28); font-size: 11px;">
            @if(in_array('division', $columns))
                <th style="padding: 10px;">Sucursal</th>
            @endif
            @if(in_array('role', $columns))
                <th style="padding: 10px;">Rol</th>
            @endif
            @if(in_array('token', $columns))
                <th style="padding: 10px; text-align: right">Código</th>
            @endif
            @if(in_array('name', $columns))
                <th style="padding: 10px; text-align: right">Nombre</th>
            @endif
            @if(in_array('moment_enter', $columns))
                <th style="padding: 10px; text-align: right">Entrada</th>
            @endif
            @if(in_array('moment_exit', $columns))
                <th style="padding: 10px; text-align: right">Salida</th>
            @endif
            @if(in_array('hours', $columns))
                <th style="padding: 10px; text-align: right">Horas</th>
            @endif
        </tr>
    </thead>
    <tbody>
        @foreach($list as $ls)
            <tr style="font-size: 10px;">
                @if(in_array('division', $columns))
                    <td>{{ $ls['division'] }}</td>
                @endif

                @if(in_array('role', $columns))
                    <td>{{ $ls['role'] }}</td>
                @endif

                @if(in_array('name', $columns))
                    <td>{{ $ls['name'] }}</td>
                @endif

                @if(in_array('token', $columns))
                    <td>{{ $ls['token'] }}</td>
                @endif

                @if(in_array('moment_enter', $columns))
                    <td>{{ $ls['moment_enter'] }}</td>
                @endif

                @if(in_array('moment_exit', $columns))
                    <td>{{ $ls['moment_exit'] }}</td>
                @endif

                @if(in_array('hours', $columns))
                    <td>{{ $ls['hours'] }}</td>
                @endif
            </tr>
        @endforeach

        <tr style="font-weight: bold; background-color: #f0f0f0;">
            <td colspan="{{ count($columns) - 1 }}" style="text-align: right;">Total horas acumuladas:</td>
            <td style="text-align: right;">{{ $total_horas }}</td>
        </tr>
    </tbody>
</table>