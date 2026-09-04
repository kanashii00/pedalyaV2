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
    public function __construct(
        protected ReportService $reportService,
        protected ReportTableBuilder $tableBuilder
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
        [$headers, $rows] = $this->tableBuilder->tableData($type, $report);

        return $this->streamExport($type, $report, $headers, $rows, true, '.xls', 'application/vnd.ms-excel');
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $type = $request->input('type', 'rental');
        $report = $this->buildReport($type, $request);
        [$headers, $rows] = $this->tableBuilder->tableData($type, $report);

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
                fputcsv($out, ['Generated: '.now()->format(ReportTableBuilder::DATE_TIME_FORMAT).' | Report ID: '.$report['reportId']]);
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
}