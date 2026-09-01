<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bicycle;
use App\Models\User;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    private const DATE_TIME_FORMAT = 'M d, Y h:i A';

    public function __construct(
        protected ReportService $reportService
    ) {}

    public function index(): Response
    {
        $bicycles = Bicycle::orderBy('name')->get();
        $users = User::where('role', 'rider')->orderBy('name')->get();

        return response()->view('admin.reports', compact('bicycles', 'users'));
    }

    public function rentalReport(Request $request): JsonResponse
    {
        try {
            $report = $this->reportService->getRentalReport($this->filters($request));

            return response()->json($report);
        } catch (\Throwable $e) {
            Log::error('Failed to generate rental report', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to generate rental report.'], 500);
        }
    }

    public function revenueReport(Request $request): JsonResponse
    {
        try {
            $report = $this->reportService->getRevenueReport([
                'start_date' => $request->input('date_from'),
                'end_date' => $request->input('date_to'),
            ], $request->input('group_by', 'month'));

            return response()->json($report);
        } catch (\Throwable $e) {
            Log::error('Failed to generate revenue report', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to generate revenue report.'], 500);
        }
    }

    public function incidentReport(Request $request): JsonResponse
    {
        try {
            $report = $this->reportService->getIncidentReport([
                'start_date' => $request->input('date_from'),
                'end_date' => $request->input('date_to'),
                'severity' => $request->input('severity'),
                'type' => $request->input('incident_type'),
            ]);

            return response()->json($report);
        } catch (\Throwable $e) {
            Log::error('Failed to generate incident report', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to generate incident report.'], 500);
        }
    }

    public function exportPdf(Request $request): Response
    {
        $type = $request->input('type', 'rental');
        $report = $this->buildReport($type, $request);

        $pdf = Pdf::loadView('admin.reports.pdf', [
            'reportType' => $type,
            'report' => $report,
            'filters' => $this->filters($request),
        ])->setPaper('a4', 'landscape');

        return $pdf->download(strtolower($type).'-report-'.date('Ymd-His').'.pdf');
    }

    public function exportExcel(Request $request): StreamedResponse
    {
        $type = $request->input('type', 'rental');
        $report = $this->buildReport($type, $request);
        [$headers, $rows] = $this->tableData($type, $report);

        return $this->streamExport($type, $report, $headers, $rows, true, '.xls', 'application/vnd.ms-excel');
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $type = $request->input('type', 'rental');
        $report = $this->buildReport($type, $request);
        [$headers, $rows] = $this->tableData($type, $report);

        return $this->streamExport($type, $report, $headers, $rows, false, '.csv', 'text/csv');
    }

    private function streamExport(
        string $type,
        array $report,
        array $headers,
        array $rows,
        bool $withSpreadsheetTitle,
        string $extension,
        string $contentType
    ): StreamedResponse {
        return response()->streamDownload(function () use ($type, $report, $headers, $rows, $withSpreadsheetTitle) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");

            if ($withSpreadsheetTitle) {
                fputcsv($out, ['Pedalya - '.ucfirst($type).' Report']);
                fputcsv($out, ['Generated: '.now()->format(self::DATE_TIME_FORMAT).' | Report ID: '.$report['reportId']]);
                fputcsv($out, []);
            }

            fputcsv($out, $headers);
            foreach ($rows as $row) {
                fputcsv($out, $row);
            }
            fclose($out);
        }, strtolower($type).'-report-'.date('Ymd-His').$extension, [
            'Content-Type' => $contentType,
        ]);
    }

    private function buildReport(string $type, Request $request): array
    {
        return match ($type) {
            'revenue' => $this->reportService->getRevenueReport([
                'start_date' => $request->input('date_from'),
                'end_date' => $request->input('date_to'),
            ], $request->input('group_by', 'month')),
            'incident' => $this->reportService->getIncidentReport([
                'start_date' => $request->input('date_from'),
                'end_date' => $request->input('date_to'),
                'severity' => $request->input('severity'),
                'type' => $request->input('incident_type'),
            ]),
            default => $this->reportService->getRentalReport([
                'start_date' => $request->input('date_from'),
                'end_date' => $request->input('date_to'),
                'status' => $request->input('status'),
                'riderId' => $request->input('user_id'),
                'bicycleId' => $request->input('bicycle_id'),
            ]),
        };
    }

    private function filters(Request $request): array
    {
        return [
            'start_date' => $request->input('date_from'),
            'end_date' => $request->input('date_to'),
            'status' => $request->input('status'),
            'riderId' => $request->input('user_id'),
            'bicycleId' => $request->input('bicycle_id'),
            'severity' => $request->input('severity'),
            'type' => $request->input('incident_type'),
        ];
    }

    private function tableData(string $type, array $report): array
    {
        return match ($type) {
            'revenue' => $this->revenueTable($report),
            'incident' => $this->incidentTable($report),
            default => $this->rentalTable($report),
        };
    }

    private function revenueTable(array $report): array
    {
        $rows = [];
        foreach ($report['data'] as $row) {
            $rows[] = [
                $row->period,
                (int) $row->total_rentals,
                (float) $row->total_revenue,
                round((float) $row->avg_revenue, 2),
                (int) $row->total_duration_minutes,
            ];
        }

        return [['Period', 'Rentals', 'Total Revenue', 'Average Revenue', 'Duration (min)'], $rows];
    }

    private function incidentTable(array $report): array
    {
        $rows = [];
        foreach ($report['data'] as $accident) {
            $rows[] = [
                $accident->id,
                $accident->type,
                $accident->severity,
                $accident->bicycle?->name ?? $accident->bicycleId,
                $accident->description,
                $this->locationLabel($accident->gpsLocation),
                $accident->status,
                $accident->acknowledged ? 'Yes' : 'No',
                $this->formatTimestamp($accident->created_at),
            ];
        }

        return [['ID', 'Type', 'Severity', 'Bicycle', 'Description', 'Location', 'Status', 'Acknowledged', 'Timestamp'], $rows];
    }

    private function rentalTable(array $report): array
    {
        $rows = [];
        foreach ($report['data'] as $rental) {
            $rows[] = [
                $rental->rentalId,
                $rental->rider?->name ?? $rental->riderName,
                $rental->bicycle?->name ?? $rental->bicycleName,
                $this->formatTimestamp($rental->startTime),
                $this->formatTimestamp($rental->endTime),
                $rental->durationMinutes ?? 0,
                (float) $rental->totalFee,
                $rental->status,
                $rental->paymentStatus,
            ];
        }

        return [['Rental ID', 'Rider', 'Bicycle', 'Start', 'End', 'Duration (min)', 'Fee', 'Status', 'Payment'], $rows];
    }

    private function locationLabel(mixed $gpsLocation): string
    {
        if (!is_array($gpsLocation) || !isset($gpsLocation['lat'], $gpsLocation['lng'])) {
            return '—';
        }

        return $gpsLocation['lat'].', '.$gpsLocation['lng'];
    }

    private function formatTimestamp(?\Illuminate\Support\Carbon $value): ?string
    {
        return $value?->format(self::DATE_TIME_FORMAT);
    }
}
