<?php

namespace App\Services;

use App\Models\Bicycle;
use App\Models\DeviceCommand;
use App\Models\DeviceStatus;
use App\Models\User;
use Carbon\Carbon;

class DeviceCommandService
{
    public function sendCommand(int $bicycleId, string $command, array $params, ?User $user = null): DeviceCommand
    {
        $bicycle = Bicycle::find($bicycleId);

        $deviceCommand = DeviceCommand::create([
            'bicycleId' => $bicycleId,
            'command' => $command,
            'params' => $params ?: null,
            'status' => 'pending',
            'issuedBy' => $user?->id,
        ]);

        if ($bicycle) {
            DeviceStatus::create([
                'bicycleId' => $bicycleId,
                'command' => $command,
                'params' => $params ?: null,
                'commandIssuedBy' => $user?->id,
                'commandIssuedAt' => Carbon::now(),
                'type' => 'command',
                'eventTimestamp' => Carbon::now(),
            ]);
        }

        return $deviceCommand;
    }

    public function acknowledgeDeviceCommand(
        int $deviceCommandId,
        ?string $result = null,
        string $status = 'executed'
    ): bool {
        $command = DeviceCommand::find($deviceCommandId);

        if (! $command) {
            return false;
        }

        $validStatus = $this->normalizeCommandStatus($status);

        $command->update([
            'status' => $validStatus,
            'executedAt' => $validStatus === 'executed' ? now() : null,
            'response' => $result,
        ]);

        if ($validStatus === 'executed') {
            $this->applyCommandEffect($command);
        }

        return true;
    }

    public function getPendingCommands(int $bicycleId): array
    {
        return DeviceCommand::where('bicycleId', $bicycleId)
            ->where('status', 'pending')
            ->orderBy('id', 'asc')
            ->take(5)
            ->get()
            ->map(fn (DeviceCommand $cmd) => [
                'id' => $cmd->id,
                'command' => $cmd->command,
                'params' => $cmd->params,
                'issued_at' => $cmd->created_at,
            ])
            ->toArray();
    }

    private function normalizeCommandStatus(string $status): string
    {
        return in_array($status, ['executed', 'failed', 'sent'], true)
            ? $status
            : 'executed';
    }

    private function applyCommandEffect(DeviceCommand $command): void
    {
        if (! in_array($command->command, ['lock', 'unlock'], true)) {
            return;
        }

        $bicycle = Bicycle::find($command->bicycleId);

        if (! $bicycle) {
            return;
        }

        $bicycle->update([
            'lockStatus' => $command->command === 'unlock'
                ? Bicycle::LOCK_UNLOCKED
                : Bicycle::LOCK_LOCKED,
            'status' => $bicycle->currentRentalId
                ? Bicycle::STATUS_RENTED
                : Bicycle::STATUS_AVAILABLE,
            'lastLockAction' => Carbon::now(),
            'lockActionBy' => $command->issuedBy,
        ]);
    }
}
