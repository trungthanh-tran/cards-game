<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Models\AuditLog;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        // Log các thay đổi
        $changes = [];
        foreach ($this->data as $key => $value) {
            if ($key === 'password') continue; // Skip password
            $oldValue = $this->record->getOriginal($key);
            if ($oldValue != $value) {
                $changes[$key] = [
                    'old' => $oldValue,
                    'new' => $value
                ];
            }
        }

        if (!empty($changes)) {
            $changeDescription = collect($changes)->map(function ($change, $key) {
                return "{$key}: {$change['old']} → {$change['new']}";
            })->implode(', ');

            AuditLog::createLog(
                action: 'edit_user',
                description: "Cập nhật thông tin người dùng: {$this->record->name}. Thay đổi: {$changeDescription}",
                user: $this->record,
                admin: auth()->user(),
                model: $this->record,
                oldValues: collect($changes)->mapWithKeys(fn ($v, $k) => [$k => $v['old']])->toArray(),
                newValues: collect($changes)->mapWithKeys(fn ($v, $k) => [$k => $v['new']])->toArray()
            );
        }
    }
}
