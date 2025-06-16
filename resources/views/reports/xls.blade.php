<table style="width: 100%">
    <thead>
        <tr style="font-weight: bold; background-color: rgba(201,201,201,0.28); font-size: 11px;">
            @if(in_array('division', $columns))
            <th style="padding: 10px;">Sucursal</th> @endif
            @if(in_array('role', $columns))
            <th style="padding: 10px;">Rol</th> @endif
            @if(in_array('token', $columns))
            <th style="padding: 10px; text-align: right">Código</th> @endif
            @if(in_array('name', $columns))
            <th style="padding: 10px; text-align: right">Nombre</th> @endif
            @if(in_array('moment_enter', $columns))
            <th style="padding: 10px; text-align: right">Entrada</th> @endif
            @if(in_array('moment_exit', $columns))
            <th style="padding: 10px; text-align: right">Salida</th> @endif
            @if(in_array('hours', $columns))
            <th style="padding: 10px; text-align: right">Horas</th> @endif
        </tr>
    </thead>
    <tbody>
        @foreach($list as $ls)
            <tr style="font-size: 10px;">
                @if(in_array('division', $columns))
                    <td style="padding: 5px; border-bottom: 1px solid rgba(169,169,169,0.29)">{{ $ls['div'] }}</td>
                @endif

                @if(in_array('role', $columns))
                    <td style="padding: 5px; border-bottom: 1px solid rgba(169,169,169,0.29)">{{ $ls['rol'] }}</td>
                @endif

                @if(in_array('token', $columns))
                    <td style="padding: 5px; text-align: right; border-bottom: 1px solid rgba(169,169,169,0.29)">
                        {{ $ls['token'] }}
                    </td>
                @endif

                @if(in_array('name', $columns))
                    <td style="padding: 5px; text-align: right; border-bottom: 1px solid rgba(169,169,169,0.29)">
                        {{ $ls['names'] }}
                    </td>
                @endif

                @if(in_array('moment_enter', $columns))
                    <td style="padding: 5px; text-align: right; border-bottom: 1px solid rgba(169,169,169,0.29)">
                        {{ $ls['moment_enter'] ? \Carbon\Carbon::parse($ls['moment_enter'])->format('d/m/y H:i') : '-' }}
                    </td>
                @endif

                @if(in_array('moment_exit', $columns))
                    <td style="padding: 5px; text-align: right; border-bottom: 1px solid rgba(169,169,169,0.29)">
                        {{ $ls['moment_exit'] ? \Carbon\Carbon::parse($ls['moment_exit'])->format('d/m/y H:i') : '-' }}
                    </td>
                @endif

                @if(in_array('hours', $columns))
                    <td style="padding: 5px; text-align: right; border-bottom: 1px solid rgba(169,169,169,0.29)">
                        {{ $ls['hours'] }}
                    </td>
                @endif
            </tr>
        @endforeach

        <tr style="font-weight: bold; background-color: #f0f0f0;">
            <td colspan="{{ count($columns) - 1 }}" style="text-align: right;">Total horas acumuladas:</td>
            <td style="text-align: right;">{{ $total_horas }}</td>
        </tr>
    </tbody>
</table>