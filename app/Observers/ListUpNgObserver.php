<?php

namespace App\Observers;

use App\Models\ListUpNg;
use App\Services\ListUpNgRecapSynchronizer;
use App\Services\ListUpNgStatusSynchronizer;

class ListUpNgObserver
{
    public function created(ListUpNg $ng): void
    {
        $this->syncStatuses([], [$ng->getAttributes()]);
        $this->syncRecapChanges([], [$ng->getAttributes()]);
    }

    public function updated(ListUpNg $ng): void
    {
        $this->syncStatuses([$ng->getRawOriginal()], [$ng->getAttributes()]);
        $this->syncRecapChanges([$ng->getRawOriginal()], [$ng->getAttributes()]);
    }

    public function deleted(ListUpNg $ng): void
    {
        $this->syncStatuses([$ng->getRawOriginal()], []);
        $this->syncRecapChanges([$ng->getRawOriginal()], []);
    }

    private function syncStatuses(array $oldRecords, array $newRecords): void
    {
        if (ListUpNgStatusSynchronizer::isAutomaticSyncSuspended()) {
            return;
        }

        app(ListUpNgStatusSynchronizer::class)->syncChanges($oldRecords, $newRecords);
    }

    private function syncRecapChanges(array $oldRecords, array $newRecords): void
    {
        if (ListUpNgRecapSynchronizer::isAutomaticSyncSuspended()) {
            return;
        }

        app(ListUpNgRecapSynchronizer::class)->syncChanges($oldRecords, $newRecords);
    }
}
