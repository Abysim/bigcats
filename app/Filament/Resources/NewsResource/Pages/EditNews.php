<?php

namespace App\Filament\Resources\NewsResource\Pages;

use App\Filament\Resources\NewsResource;
use App\Models\News;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Throwable;

class EditNews extends EditRecord
{
    protected static string $resource = NewsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make('view')
                ->url(fn ($record) => route('filament.app.resources.news.view', [
                    $record->year,
                    $record->month,
                    $record->day,
                    $record->slug,
                ]))
                ->color('info'),
        ];
    }

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction(),
            Action::make('saveAndView')
                ->label('Зберегти та переглянути')
                ->action('saveAndView')
                ->keyBindings(['mod+shift+s'])
                ->color('info'),
            $this->getCancelFormAction(),
            DeleteAction::make(),
        ];
    }

    public function saveAndView(): void
    {
        try {
            /** @var News $record */
            $record = $this->record;
            $this->save(false);
            $this->redirect(route('filament.app.resources.news.view', [
                $record->year,
                $record->month,
                $record->day,
                $record->slug,
            ]));
        } catch (Throwable $e) {
            Notification::make()
                ->danger()
                ->title($e->getMessage())
                ->send();
        }
    }
}
