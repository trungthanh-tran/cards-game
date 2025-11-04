<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;
use App\Models\AuditLog;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function afterCreate(): void
    {
        AuditLog::createLog(
            action: 'create_user',
            description: "Tạo người dùng mới: {$this->record->name} ({$this->record->email})",
            user: $this->record,
            admin: auth()->user()
        );
    }
}