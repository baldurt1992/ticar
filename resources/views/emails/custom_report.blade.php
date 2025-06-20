<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte personalizado</title>
</head>

<body>
    <p>Hola,</p>

    <p>Adjunto encontrarás el reporte personalizado <strong>{{ $report->name }}</strong>.</p>

    <p>Este reporte fue generado automáticamente el {{ now()->format('d/m/Y H:i') }}.</p>

    <p>Saludos,<br>Equipo de Reportes</p>
</body>

</html>