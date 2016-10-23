<?php
namespace Runalyze\Sync\Provider\TomTomMySports;
use Runalyze\Sync\Provider\ActivitySyncInterface;
use Runalyze\Profile\SyncProvider;

class TomTomMySports implements ActivitySyncInterface {

    public static function getIdentifier() {
        return SyncProvider::TOMTOM_MYSPORTS;
    }

    public function fetchActivityList() {
        //TODO
    }

    public function fetchActivity($identifier) {
        //TODO
    }
}
