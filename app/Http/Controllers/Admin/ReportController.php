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

    public function index(Request $request): Response
    {
        $reportType = $request->query('tab', 'customer');
        $allowed = ['customer', 'rental', 'bicycle', 'theft', 'accident', 'revenue', 'export'];
        $reportType = in_array($reportType, $allowed, true) ? $reportType : 'customer';

        $bicycles = Bicycle::orderBy('name')->get();
        $users = User::where('role', 'rider')->orderBy('name')->get();

        return response()->view('admin.reports', compact('bicycles', 'users', 'reportType'));
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
            $report = $this->reportService->getIncidentReport($this->filters($request));

            return response()->json($report);
        } catch (\Throwable $e) {
            Log::error('Failed to generate incident report', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to generate incident report.'], 500);
        }
    }

    public function accidentReport(Request $request): JsonResponse
    {
        try {
            $report = $this->reportService->getAccidentReport($this->filters($request));

            return response()->json($report);
        } catch (\Throwable $e) {
            Log::error('Failed to generate accident report', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to generate accident report.'], 500);
        }
    }

    public function customerReport(Request $request): JsonResponse
    {
        try {
            $report = $this->reportService->getCustomerReport($this->filters($request));

            return response()->json($report);
        } catch (\Throwable $e) {
            Log::error('Failed to generate customer report', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to generate customer report.'], 500);
        }
    }

    public function bicycleReport(Request $request): JsonResponse
    {
        try {
            $report = $this->reportService->getBicycleReport($this->filters($request));

            return response()->json($report);
        } catch (\Throwable $e) {
            Log::error('Failed to generate bicycle report', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to generate bicycle report.'], 500);
        }
    }

    public function theftReport(Request $request): JsonResponse
    {
        try {
            $report = $this->reportService->getTheftReport($this->filters($request));

            return response()->json($report);
        } catch (\Throwable $e) {
            Log::error('Failed to generate theft report', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to generate theft report.'], 500);
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
            'customer' => $this->reportService->getCustomerReport($this->filters($request)),
            'accident' => $this->reportService->getAccidentReport($this->filters($request)),
            'bicycle' => $this->reportService->getBicycleReport($this->filters($request)),
            'theft' => $this->reportService->getTheftReport($this->filters($request)),
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
            default => $this->reportService->getRentalReport($this->filters($request)),
        };
    }

    private function filters(Request $request): array
    {
        return [
            'start_date' => $request->input('date_from'),
            'end_date' => $request->input('date_to'),
            'status' => $request->input('status'),
            'payment_status' => $request->input('payment_status'),
            'riderId' => $request->input('user_id'),
            'bicycleId' => $request->input('bicycle_id'),
            'severity' => $request->input('severity'),
            'type' => $request->input('incident_type'),
            'verified' => $request->input('verified'),
            'search' => $request->input('search'),
        ];
    }

    private function tableData(string $type, array $report): array
    {
        return match ($type) {
            'customer' => $this->customerTable($report),
            'revenue' => $this->revenueTable($report),
            'accident' => $this->accidentTable($report),
            'incident' => $this->incidentTable($report),
            'bicycle' => $this->bicycleTable($report),
            'theft' => $this->theftTable($report),
            default => $this->rentalTable($report),
        };
    }

    private function accidentTable(array $report): array
    {
        $rows = [];
        foreach ($report['data'] as $accident) {
            $rows[] = [
                '#' . $accident->id,
                $accident->rider?->name ?? $accident->reportedBy ?? '—',
                $accident->bicycle?->name ?? $accident->bicycleId,
                $this->locationLabel($accident->gpsLocation),
                $this->formatTimestamp($accident->created_at),
                ucfirst($accident->severity ?? '—'),
                $accident->status,
                $accident->acknowledged ? 'Yes' : 'No',
                $accident->actionTaken ?? '—',
            ];
        }

        return [['Accident ID', 'Rider', 'Bicycle', 'Location', 'Date/Time', 'Severity', 'Status', 'Acknowledged', 'Action Taken'], $rows];
    }

    private function customerTable(array $report): array
    {
        $rows = [];
        foreach ($report['data'] as $customer) {
            $rows[] = [
                $customer->name,
                $customer->studentId ?? '—',
                $customer->email,
                $customer->phoneNumber ?? '—',
                $customer->status,
                $customer->verified ? 'Yes' : 'No',
                $customer->totalRentals ?? 0,
                '₱' . number_format((float) ($customer->totalSpent ?? 0), 2),
                $customer->created_at?->format(self::DATE_TIME_FORMAT),
            ];
        }

        return [['Name', 'Student ID', 'Email', 'Phone', 'Status', 'Verified', 'Rentals', 'Total Spent', 'Joined'], $rows];
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

    private function bicycleTable(array $report): array
    {
        $rows = [];
        foreach ($report['data'] as $bicycle) {
            $completed = $bicycle->rentals->whereIn('status', ['completed', 'returned']);
            $rows[] = [
                $bicycle->name,
                $bicycle->model ?? '—',
                ucfirst($bicycle->status ?? '—'),
                $bicycle->batteryLevel !== null ? $bicycle->batteryLevel . '%' : '—',
                $bicycle->totalRentals ?? 0,
                $bicycle->totalDistance ? number_format((float) $bicycle->totalDistance, 2) . ' km' : '0.00 km',
                '₱' . number_format((float) $completed->sum('totalFee'), 2),
                $bicycle->condition ?? '—',
            ];
        }

        return [['Bicycle', 'Model', 'Status', 'Battery', 'Total Rentals', 'Total Distance', 'Total Revenue', 'Condition'], $rows];
    }

    private function theftTable(array $report): array
    {
        $rows = [];
        foreach ($report['data'] as $theft) {
            $rows[] = [
                '#' . $theft->id,
                $theft->bicycle?->name ?? $theft->bicycleId,
                ucfirst($theft->severity ?? '—'),
                $theft->description ?? '—',
                $this->locationLabel($theft->gpsLocation),
                $theft->status,
                $theft->acknowledged ? 'Yes' : 'No',
                $this->formatTimestamp($theft->created_at),
            ];
        }

        return [['Theft ID', 'Bicycle', 'Severity', 'Description', 'Location', 'Status', 'Acknowledged', 'Timestamp'], $rows];
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
                (float) ($rental->ratePerHour ?? 0),
                (float) $rental->totalFee,
                $rental->paymentMethod ?? '—',
                $rental->paymentStatus,
                $rental->status,
            ];
        }

        return [['Rental ID', 'Rider', 'Bicycle', 'Start', 'End', 'Duration (min)', 'Rate/Hour', 'Fee', 'Payment Method', 'Payment', 'Status'], $rows];
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
