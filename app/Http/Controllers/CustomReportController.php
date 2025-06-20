<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CustomReport;
use Carbon\Carbon;

class CustomReportController extends Controller
{
    public function index()
    {
        return view('reports.custom');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'nullable|string|max:255',
            'columns' => 'required|array',
            'filters' => 'nullable|array',
            'format' => 'required|in:pdf,excel,both',
            'schedule' => 'required|in:daily,weekly,monthly,custom',
            'custom_day' => 'nullable|integer|min:1|max:31',
            'custom_time' => 'nullable|date_format:H:i',
            'emails' => 'required|array|min:1',
            'emails.*' => 'email',
        ]);

        $data['user_id'] = 1;

        if (empty($data['custom_time'])) {
            $data['custom_time'] = '06:00';
        }

        $data['cron'] = $this->buildCron(
            $data['schedule'],
            $data['custom_day'],
            $data['custom_time'],
            $data['timezone'] ?? 'America/Bogota'
        );

        $report = CustomReport::create($data);

        return response()->json(['message' => 'Reporte personalizado creado correctamente', 'report' => $report]);
    }

    private function buildCron($schedule, $day = null, $time = null, $timezone = null)
    {
        $time = $time ?? '06:00';
        $timezone = $timezone ?? 'America/Bogota';

        try {
            $localTime = Carbon::createFromFormat('H:i', $time, $timezone)->setTimezone('UTC');
            $hour = $localTime->format('H');
            $minute = $localTime->format('i');
        } catch (\Exception $e) {
            $hour = '06';
            $minute = '00';
        }

        return match ($schedule) {
            'daily' => "{$minute} {$hour} * * *",
            'weekly' => "{$minute} {$hour} * * 1",
            'monthly' => "{$minute} {$hour} 1 * *",
            'custom' => "{$minute} {$hour} {$day} * *",
            default => "{$minute} {$hour} * * *",
        };
    }

}
