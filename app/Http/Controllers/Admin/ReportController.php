<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bicycle;
use App\Models\User;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ) {}

    public function index(): Response
    {
        $bicycles = Bicycle::orderBy('name')->get();
        $users = User::where('role', 'rider')->orderBy('name')->get();

        return response()->view('admin.reports', compact('bicycles', 'users'));
    }

    public function rentalReport(Request $request): Response
    {
        $report = $this->reportService->getRentalReport($this->filters($request));

        return response()->json($report);
    }

    public function revenueReport(Request $request): Response
    {
        $report = $this->reportService->getRevenueReport([
            'start_date' => $request->input('date_from'),
            'end_date'   => $request->input('date_to'),
        ], $request->input('group_by', 'month'));

        return response()->json($report);
    }

    public function incidentReport(Request $request): Response
    {
        $report = $this->reportService->getIncidentReport([
            'start_date' => $request->input('date_from'),
            'end_date'   => $request->input('date_to'),
            'severity'   => $request->input('severity'),
            'type'       => $request->input('incident_type'),
        ]);

        return response()->json($report);
    }

    public function exportPdf(Request $request): Response
    {
        $type = $request->input('type', 'rental');
        $report = $this->buildReport($type, $request);

        $pdf = Pdf::loadView('admin.reports.pdf', [
            'reportType' => $type,
            'report'     => $report,
            'filters'    => $this->filters($request),
        ])->setPaper('a4', 'landscape');

        return $pdf->download(strtolower($type).'-report-'.date('Ymd-His').'.pdf');
    }

    public function exportExcel(Request $request): StreamedResponse
    {
        $type = $request->input('type', 'rental');
        $report = $this->buildReport($type, $request);
        [$headers, $rows] = $this->tableData($type, $report);

        return response()->streamDownload(function () use ($headers, $rows, $report) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Pedalya - ' . ucfirst($type) . ' Report']);
            fputcsv($out, ['Generated: ' . now()->format('M d, Y h:i A') . ' | Report ID: ' . $report['reportId']]);
            fputcsv($out, []);
            fputcsv($out, $headers);
            foreach ($rows as $row) {
                fputcsv($out, $row);
            }
            fclose($out);
        }, strtolower($type).'-report-'.date('Ymd-His').'.xls', [
            'Content-Type' => 'application/vnd.ms-excel',
        ]);
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $type = $request->input('type', 'rental');
        $report = $this->buildReport($type, $request);
        [$headers, $rows] = $this->tableData($type, $report);

        return response()->streamDownload(function () use ($headers, $rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, $headers);
            foreach ($rows as $row) {
                fputcsv($out, $row);
            }
            fclose($out);
        }, strtolower($type).'-report-'.date('Ymd-His').'.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function buildReport(string $type, Request $request): array
    {
        return match ($type) {
            'revenue' => $this->reportService->getRevenueReport([
                'start_date' => $request->input('date_from'),
                'end_date'   => $request->input('date_to'),
            ], $request->input('group_by', 'month')),
            'incident' => $this->reportService->getIncidentReport([
                'start_date' => $request->input('date_from'),
                'end_date'   => $request->input('date_to'),
                'severity'   => $request->input('severity'),
                'type'       => $request->input('incident_type'),
            ]),
            default => $this->reportService->getRentalReport([
                'start_date' => $request->input('date_from'),
                'end_date'   => $request->input('date_to'),
                'status'     => $request->input('status'),
                'riderId'    => $request->input('user_id'),
                'bicycleId'  => $request->input('bicycle_id'),
            ]),
        };
    }

    private function filters(Request $request): array
    {
        return [
            'start_date' => $request->input('date_from'),
            'end_date'   => $request->input('date_to'),
            'status'     => $request->input('status'),
            'riderId'    => $request->input('user_id'),
            'bicycleId'  => $request->input('bicycle_id'),
            'severity'   => $request->input('severity'),
            'type'       => $request->input('type'),
        ];
    }

    private function tableData(string $type, array $report): array
    {
        $rows = [];

        if ($type === 'revenue') {
            $headers = ['Period', 'Rentals', 'Total Revenue', 'Average Revenue', 'Duration (min)'];
            foreach ($report['data'] as $row) {
                $rows[] = [
                    $row->period,
                    (int) $row->total_rentals,
                    (float) $row->total_revenue,
                    round((float) $row->avg_revenue, 2),
                    (int) $row->total_duration_minutes,
                ];
            }
        } elseif ($type === 'incident') {
            $headers = ['ID', 'Type', 'Severity', 'Bicycle', 'Description', 'Location', 'Status', 'Acknowledged', 'Timestamp'];
            foreach ($report['data'] as $accident) {
                $loc = is_array($accident->gpsLocation) ? $accident->gpsLocation : [];
                $rows[] = [
                    $accident->id,
                    $accident->type,
                    $accident->severity,
                    $accident->bicycle?->name ?? $accident->bicycleId,
                    $accident->description,
                    isset($loc['lat'], $loc['lng']) ? $loc['lat'].', '.$loc['lng'] : '—',
                    $accident->status,
                    $accident->acknowledged ? 'Yes' : 'No',
                    $accident->created_at?->format('M d, Y h:i A'),
                ];
            }
        } else {
            $headers = ['Rental ID', 'Rider', 'Bicycle', 'Start', 'End', 'Duration (min)', 'Fee', 'Status', 'Payment'];
            foreach ($report['data'] as $rental) {
                $rows[] = [
                    $rental->rentalId,
                    $rental->rider?->name ?? $rental->riderName,
                    $rental->bicycle?->name ?? $rental->bicycleName,
                    $rental->startTime?->format('M d, Y h:i A'),
                    $rental->endTime?->format('M d, Y h:i A'),
                    $rental->durationMinutes ?? 0,
                    (float) $rental->totalFee,
                    $rental->status,
                    $rental->paymentStatus,
                ];
            }
        }

        return [$headers, $rows];
    }
}
