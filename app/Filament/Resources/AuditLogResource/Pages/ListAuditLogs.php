<?php

namespace App\Filament\Resources\AuditLogResource\Pages;

use App\Filament\Resources\AuditLogResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

class ListAuditLogs extends ListRecords
{
    protected static string $resource = AuditLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('export')
                ->label('Export CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function () {
                    return response()->streamDownload(function () {
                        $logs = \App\Models\AuditLog::with(['user', 'admin'])
                            ->latest()
                            ->get();
                        
                        $csv = fopen('php://output', 'w');
                        
                        // Header
                        fputcsv($csv, [
                            'ID', 'Thời gian', 'Hành động', 'Admin', 
                            'User', 'Mô tả', 'IP Address'
                        ]);
                        
                        // Data
                        foreach ($logs as $log) {
                            fputcsv($csv, [
                                $log->id,
                                $log->created_at->format('d/m/Y H:i:s'),
                                $log->action,
                                $log->admin?->name ?? 'Hệ thống',
                                $log->user?->name ?? '-',
                                $log->description,
                                $log->ip_address,
                            ]);
                        }
                        
                        fclose($csv);
                    }, 'audit-logs-' . now()->format('Y-m-d') . '.csv');
                }),
        ];
    }
}