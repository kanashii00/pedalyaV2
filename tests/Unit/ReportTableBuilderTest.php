<?php

namespace Tests\Unit;

use App\Http\Controllers\Admin\ReportTableBuilder;
use App\Models\Accident;
use App\Models\Bicycle;
use App\Models\Rental;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesTestData;
use Tests\TestCase;

class ReportTableBuilderTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestData;

    private ReportTableBuilder $builder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->builder = new ReportTableBuilder();
    }

    public function test_table_data_dispatches_to_customer_table(): void
    {
        $rider = $this->makeRider([
            'name' => 'Jane',
            'studentId' => 'S123',
            'email' => 'jane@test.com',
            'phoneNumber' => '09171234567',
            'status' => 'active',
            'verified' => true,
            'created_at' => Carbon::parse('2026-01-15 10:30:00'),
        ]);

        $rental = $this->makeRental([
            'riderId' => $rider->id,
            'status' => Rental::STATUS_COMPLETED,
            'totalFee' => 50.00,
        ]);

        $report = [
            'data' => collect([
                (object) [
                    'name' => $rider->name,
                    'studentId' => $rider->studentId,
                    'email' => $rider->email,
                    'phoneNumber' => $rider->phoneNumber,
                    'status' => $rider->status,
                    'verified' => $rider->verified,
                    'totalRentals' => 1,
                    'totalSpent' => 50.00,
                    'created_at' => $rider->created_at,
                ],
            ]),
        ];

        [$headers, $rows] = $this->builder->tableData('customer', $report);

        $this->assertSame(['Name', 'Student ID', 'Email', 'Phone', 'Status', 'Verified', 'Rentals', 'Total Spent', 'Joined'], $headers);
        $this->assertCount(1, $rows);
        $this->assertSame('Jane', $rows[0][0]);
        $this->assertSame('S123', $rows[0][1]);
        $this->assertSame('Yes', $rows[0][5]);
        $this->assertSame('₱50.00', $rows[0][7]);
    }

    public function test_customer_table_handles_nullable_fields(): void
    {
        $report = [
            'data' => collect([
                (object) [
                    'name' => 'No Phone',
                    'studentId' => null,
                    'email' => 'nophone@test.com',
                    'phoneNumber' => null,
                    'status' => 'active',
                    'verified' => false,
                    'totalRentals' => 0,
                    'totalSpent' => 0,
                    'created_at' => null,
                ],
            ]),
        ];

        [$headers, $rows] = $this->builder->tableData('customer', $report);

        $this->assertSame('—', $rows[0][1]);
        $this->assertSame('—', $rows[0][3]);
        $this->assertSame('No', $rows[0][5]);
        $this->assertSame('₱0.00', $rows[0][7]);
        $this->assertNull($rows[0][8]);
    }

    public function test_revenue_table(): void
    {
        $report = [
            'data' => collect([
                (object) [
                    'period' => '2026-01',
                    'total_rentals' => 5,
                    'total_revenue' => 150.00,
                    'avg_revenue' => 30.00,
                    'total_duration_minutes' => 300,
                ],
            ]),
        ];

        [$headers, $rows] = $this->builder->tableData('revenue', $report);

        $this->assertSame(['Period', 'Rentals', 'Total Revenue', 'Average Revenue', 'Duration (min)'], $headers);
        $this->assertCount(1, $rows);
        $this->assertSame('2026-01', $rows[0][0]);
        $this->assertSame(5, $rows[0][1]);
    }

    public function test_accident_table(): void
    {
        $bike = $this->makeBicycle(['name' => 'Accident Bike']);
        $rider = $this->makeRider(['name' => 'Accident Rider']);

        $report = [
            'data' => collect([
                (object) [
                    'id' => 1,
                    'rider' => $rider,
                    'bicycle' => $bike,
                    'bicycleId' => $bike->id,
                    'reportedBy' => null,
                    'gpsLocation' => ['lat' => 14.6, 'lng' => 120.98],
                    'created_at' => Carbon::parse('2026-03-01 14:30:00'),
                    'severity' => 'critical',
                    'status' => 'open',
                    'acknowledged' => true,
                    'actionTaken' => 'Called ambulance',
                ],
            ]),
        ];

        [$headers, $rows] = $this->builder->tableData('accident', $report);

        $this->assertSame(['Accident ID', 'Rider', 'Bicycle', 'Location', 'Date/Time', 'Severity', 'Status', 'Acknowledged', 'Action Taken'], $headers);
        $this->assertCount(1, $rows);
        $this->assertSame('#1', $rows[0][0]);
        $this->assertSame('Accident Rider', $rows[0][1]);
        $this->assertSame('Accident Bike', $rows[0][2]);
        $this->assertSame('14.6, 120.98', $rows[0][3]);
        $this->assertSame('Critical', $rows[0][5]);
        $this->assertSame('Yes', $rows[0][7]);
    }

    public function test_accident_table_with_null_rider_and_bicycle(): void
    {
        $report = [
            'data' => collect([
                (object) [
                    'id' => 2,
                    'rider' => null,
                    'bicycle' => null,
                    'bicycleId' => 99,
                    'reportedBy' => 'Admin Report',
                    'gpsLocation' => null,
                    'created_at' => null,
                    'severity' => null,
                    'status' => 'closed',
                    'acknowledged' => false,
                    'actionTaken' => null,
                ],
            ]),
        ];

        [$headers, $rows] = $this->builder->tableData('accident', $report);

        $this->assertSame('Admin Report', $rows[0][1]);
        $this->assertSame(99, $rows[0][2]);
        $this->assertSame('—', $rows[0][3]);
        $this->assertSame('—', $rows[0][5]);
        $this->assertSame('No', $rows[0][7]);
        $this->assertSame('—', $rows[0][8]);
    }

    public function test_incident_table(): void
    {
        $bike = $this->makeBicycle(['name' => 'Incident Bike']);

        $report = [
            'data' => collect([
                (object) [
                    'id' => 10,
                    'type' => 'geofence_breach',
                    'severity' => 'major',
                    'bicycle' => $bike,
                    'bicycleId' => $bike->id,
                    'description' => 'Left zone',
                    'gpsLocation' => ['lat' => 14.59, 'lng' => 120.97],
                    'status' => 'open',
                    'acknowledged' => false,
                    'created_at' => Carbon::parse('2026-03-02 09:00:00'),
                ],
            ]),
        ];

        [$headers, $rows] = $this->builder->tableData('incident', $report);

        $this->assertSame(['ID', 'Type', 'Severity', 'Bicycle', 'Description', 'Location', 'Status', 'Acknowledged', 'Timestamp'], $headers);
        $this->assertCount(1, $rows);
        $this->assertSame(10, $rows[0][0]);
        $this->assertSame('geofence_breach', $rows[0][1]);
        $this->assertSame('Incident Bike', $rows[0][3]);
        $this->assertSame('No', $rows[0][7]);
    }

    public function test_incident_table_with_null_bicycle(): void
    {
        $report = [
            'data' => collect([
                (object) [
                    'id' => 11,
                    'type' => 'impact',
                    'severity' => 'minor',
                    'bicycle' => null,
                    'bicycleId' => 42,
                    'description' => 'Impact',
                    'gpsLocation' => null,
                    'status' => 'resolved',
                    'acknowledged' => true,
                    'created_at' => null,
                ],
            ]),
        ];

        [$headers, $rows] = $this->builder->tableData('incident', $report);

        $this->assertSame(42, $rows[0][3]);
        $this->assertSame('—', $rows[0][5]);
        $this->assertSame('Yes', $rows[0][7]);
        $this->assertNull($rows[0][8]);
    }

    public function test_bicycle_table(): void
    {
        $bike = $this->makeBicycle([
            'name' => 'City Pro',
            'model' => 'V2',
            'status' => 'available',
            'batteryLevel' => 85,
            'totalRentals' => 10,
            'totalDistance' => 150.5,
            'condition' => 'good',
        ]);

        $rental = $this->makeRental([
            'bicycleId' => $bike->id,
            'status' => Rental::STATUS_COMPLETED,
            'totalFee' => 30.00,
        ]);

        $report = [
            'data' => collect([
                (object) [
                    'name' => $bike->name,
                    'model' => $bike->model,
                    'status' => $bike->status,
                    'batteryLevel' => $bike->batteryLevel,
                    'totalRentals' => $bike->totalRentals,
                    'totalDistance' => $bike->totalDistance,
                    'condition' => $bike->condition,
                    'rentals' => $bike->rentals,
                ],
            ]),
        ];

        [$headers, $rows] = $this->builder->tableData('bicycle', $report);

        $this->assertSame(['Bicycle', 'Model', 'Status', 'Battery', 'Total Rentals', 'Total Distance', 'Total Revenue', 'Condition'], $headers);
        $this->assertCount(1, $rows);
        $this->assertSame('City Pro', $rows[0][0]);
        $this->assertSame('V2', $rows[0][1]);
        $this->assertSame('Available', $rows[0][2]);
        $this->assertSame('85%', $rows[0][3]);
        $this->assertSame(10, $rows[0][4]);
        $this->assertSame('150.50 km', $rows[0][5]);
        $this->assertSame('good', $rows[0][7]);
    }

    public function test_bicycle_table_null_fields(): void
    {
        $report = [
            'data' => collect([
                (object) [
                    'name' => 'Old Bike',
                    'model' => null,
                    'status' => null,
                    'batteryLevel' => null,
                    'totalRentals' => null,
                    'totalDistance' => null,
                    'condition' => null,
                    'rentals' => collect(),
                ],
            ]),
        ];

        [$headers, $rows] = $this->builder->tableData('bicycle', $report);

        $this->assertSame('—', $rows[0][1]);
        $this->assertSame('—', $rows[0][2]);
        $this->assertSame('—', $rows[0][3]);
        $this->assertSame('0.00 km', $rows[0][5]);
        $this->assertSame('₱0.00', $rows[0][6]);
        $this->assertSame('—', $rows[0][7]);
    }

    public function test_theft_table(): void
    {
        $bike = $this->makeBicycle(['name' => 'Stolen Bike']);

        $report = [
            'data' => collect([
                (object) [
                    'id' => 5,
                    'bicycle' => $bike,
                    'bicycleId' => $bike->id,
                    'severity' => 'critical',
                    'description' => 'Bike stolen from rack',
                    'gpsLocation' => ['lat' => 14.7, 'lng' => 121.0],
                    'status' => 'open',
                    'acknowledged' => true,
                    'created_at' => Carbon::parse('2026-04-01 08:00:00'),
                ],
            ]),
        ];

        [$headers, $rows] = $this->builder->tableData('theft', $report);

        $this->assertSame(['Theft ID', 'Bicycle', 'Severity', 'Description', 'Location', 'Status', 'Acknowledged', 'Timestamp'], $headers);
        $this->assertCount(1, $rows);
        $this->assertSame('#5', $rows[0][0]);
        $this->assertSame('Stolen Bike', $rows[0][1]);
        $this->assertSame('Critical', $rows[0][2]);
        $this->assertSame('14.7, 121', $rows[0][4]);
        $this->assertSame('Yes', $rows[0][6]);
    }

    public function test_theft_table_null_fields(): void
    {
        $report = [
            'data' => collect([
                (object) [
                    'id' => 6,
                    'bicycle' => null,
                    'bicycleId' => 33,
                    'severity' => null,
                    'description' => null,
                    'gpsLocation' => null,
                    'status' => 'resolved',
                    'acknowledged' => false,
                    'created_at' => null,
                ],
            ]),
        ];

        [$headers, $rows] = $this->builder->tableData('theft', $report);

        $this->assertSame(33, $rows[0][1]);
        $this->assertSame('—', $rows[0][2]);
        $this->assertSame('—', $rows[0][3]);
        $this->assertSame('—', $rows[0][4]);
        $this->assertSame('No', $rows[0][6]);
        $this->assertNull($rows[0][7]);
    }

    public function test_rental_table_default_case(): void
    {
        $rider = $this->makeRider(['name' => 'Rental Rider']);
        $bike = $this->makeBicycle(['name' => 'Rental Bike']);

        $report = [
            'data' => collect([
                (object) [
                    'rentalId' => 'REN-20260301-001',
                    'rider' => $rider,
                    'riderName' => $rider->name,
                    'bicycle' => $bike,
                    'bicycleName' => $bike->name,
                    'startTime' => Carbon::parse('2026-03-01 10:00:00'),
                    'endTime' => Carbon::parse('2026-03-01 11:00:00'),
                    'durationMinutes' => 60,
                    'ratePerHour' => 15.00,
                    'totalFee' => 15.00,
                    'paymentMethod' => 'cash',
                    'paymentStatus' => 'paid',
                    'status' => 'completed',
                ],
            ]),
        ];

        [$headers, $rows] = $this->builder->tableData('rental', $report);

        $this->assertSame(['Rental ID', 'Rider', 'Bicycle', 'Start', 'End', 'Duration (min)', 'Rate/Hour', 'Fee', 'Payment Method', 'Payment', 'Status'], $headers);
        $this->assertCount(1, $rows);
        $this->assertSame('REN-20260301-001', $rows[0][0]);
        $this->assertSame('Rental Rider', $rows[0][1]);
        $this->assertSame('Rental Bike', $rows[0][2]);
        $this->assertSame('cash', $rows[0][8]);
        $this->assertSame('completed', $rows[0][10]);
    }

    public function test_rental_table_with_null_related_models(): void
    {
        $report = [
            'data' => collect([
                (object) [
                    'rentalId' => 'REN-000',
                    'rider' => null,
                    'riderName' => 'Deleted Rider',
                    'bicycle' => null,
                    'bicycleName' => 'Deleted Bike',
                    'startTime' => null,
                    'endTime' => null,
                    'durationMinutes' => null,
                    'ratePerHour' => null,
                    'totalFee' => 0,
                    'paymentMethod' => null,
                    'paymentStatus' => 'pending',
                    'status' => 'cancelled',
                ],
            ]),
        ];

        [$headers, $rows] = $this->builder->tableData('rental', $report);

        $this->assertSame('Deleted Rider', $rows[0][1]);
        $this->assertSame('Deleted Bike', $rows[0][2]);
        $this->assertSame(0, $rows[0][5]);
        $this->assertSame('—', $rows[0][8]);
    }

    public function test_unknown_type_falls_through_to_rental_table(): void
    {
        $report = [
            'data' => collect([
                (object) [
                    'rentalId' => 'REN-FALL',
                    'rider' => null,
                    'riderName' => 'X',
                    'bicycle' => null,
                    'bicycleName' => 'Y',
                    'startTime' => null,
                    'endTime' => null,
                    'durationMinutes' => 0,
                    'ratePerHour' => 0,
                    'totalFee' => 0,
                    'paymentMethod' => null,
                    'paymentStatus' => 'pending',
                    'status' => 'pending',
                ],
            ]),
        ];

        [$headers, $rows] = $this->builder->tableData('unknown', $report);

        $this->assertSame('Rental ID', $headers[0]);
    }

    public function test_location_label_with_valid_gps(): void
    {
        $report = ['data' => collect()];
        $method = new \ReflectionMethod(ReportTableBuilder::class, 'locationLabel');

        $result = $method->invoke($this->builder, ['lat' => 14.5995, 'lng' => 120.9842]);
        $this->assertSame('14.5995, 120.9842', $result);
    }

    public function test_location_label_with_null(): void
    {
        $method = new \ReflectionMethod(ReportTableBuilder::class, 'locationLabel');

        $this->assertSame('—', $method->invoke($this->builder, null));
    }

    public function test_location_label_with_missing_keys(): void
    {
        $method = new \ReflectionMethod(ReportTableBuilder::class, 'locationLabel');

        $this->assertSame('—', $method->invoke($this->builder, ['lat' => 14.0]));
    }

    public function test_location_label_with_non_array(): void
    {
        $method = new \ReflectionMethod(ReportTableBuilder::class, 'locationLabel');

        $this->assertSame('—', $method->invoke($this->builder, 'invalid'));
    }

    public function test_format_timestamp_with_value(): void
    {
        $method = new \ReflectionMethod(ReportTableBuilder::class, 'formatTimestamp');
        $result = $method->invoke($this->builder, Carbon::parse('2026-03-01 14:30:00'));

        $this->assertSame('Mar 01, 2026 02:30 PM', $result);
    }

    public function test_format_timestamp_with_null(): void
    {
        $method = new \ReflectionMethod(ReportTableBuilder::class, 'formatTimestamp');

        $this->assertNull($method->invoke($this->builder, null));
    }
}
