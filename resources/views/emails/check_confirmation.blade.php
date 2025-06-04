<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Confirmación</title>
</head>

<body>
    <p>Hola {{ $person->names }},</p>
    <p>Tu {{ $tipo }} ha sido registrada correctamente el día {{ \Carbon\Carbon::parse($hora)->format('d/m/Y H:i:s') }}.
    </p>
    <p>Gracias.</p>
</body>

</html>