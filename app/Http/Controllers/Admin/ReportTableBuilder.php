<?php

namespace App\Http\Controllers\Admin;

/**
 * Builds the tabular row/column data used by report CSV/Excel/PDF exports.
 *
 * Splitting the per-report table builders out of ReportController keeps that
 * controller focused on HTTP actions while this class owns the export-format
 * translation layer.
 */
class ReportTableBuilder
{
    public const DATE_TIME_FORMAT = 'M d, Y h:i A';

    public function tableData(string $type, array $report): array
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
