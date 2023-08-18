<?php
namespace Dvk\Feature;

use Bitrix\Main\Application;
use Bitrix\Main\Data\Cache;
use Bitrix\Main\EventManager;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\SystemException;

/**
 * the class is inherited by all its modules
 */
class Module {

    const MODULE_ID = 'dvk.feature';

    const CACHE_TIME = 86400;

    /**
     * @return array|null
     * @throws SystemException
     */
    public static function checkEvents(): ?array
    {
        $cache = Cache::createInstance();
        $cache_id = static::MODULE_ID . '_EVENTS';
        if ($cache->initCache(static::CACHE_TIME, $cache_id, static::MODULE_ID)) {
            return $cache->getVars();
        }

        $MODULE_EVENTS = array();
        $event_file = realpath(__DIR__ . '/../../' . static::MODULE_ID) . '/install/events.php';
        if(!file_exists($event_file)) {
            $event_file = $_SERVER['DOCUMENT_ROOT'] . '/local/modules/' . static::MODULE_ID . '/install/events.php';
        }
        include($event_file);

        if(!is_array($MODULE_EVENTS) || count($MODULE_EVENTS) < 1) {
            return null;
        }

        $connection = Application::getConnection();
        $sql = "SELECT * FROM b_module_to_module WHERE TO_MODULE_ID = '" . static::MODULE_ID . "'";
        $event_rows = $connection->query($sql)->fetchAll();

        $ex_events_res = array();
        foreach($event_rows as $event_row) {
            $event_item = array(
                (string) $event_row['FROM_MODULE_ID'],
                (string) $event_row['MESSAGE_ID'],
                (string) $event_row['TO_MODULE_ID'],
                (string) $event_row['TO_CLASS'],
                (string) $event_row['TO_METHOD'],
                (string) $event_row['SORT'],
                (string) $event_row['VERSION'],
            );
            $event_hash = md5(json_encode($event_item));
            $ex_events_res[$event_hash] = $event_item;
        }

        $manager = EventManager::getInstance();

        foreach($MODULE_EVENTS as $event) {

            if(count($event) < 1 || empty($event[0])) {
                continue;
            }

            if(count($event) < 5) {
                throw new SystemException('Incomplete event item ' . $event[1]);
            }
            $event = array_map('strval', $event);
            if(empty($event[5])) {
                $event[5] = "100";
            }
            if(empty($event[6])) {
                $event[6] = "2";
            }

            $event_hash = md5(json_encode($event));

            if(array_key_exists($event_hash, $ex_events_res)) {
                unset($ex_events_res[$event_hash]);
                continue;
            }

            if($event[6] != "2") {
                $manager->registerEventHandlerCompatible($event[0], $event[1], $event[2], $event[3], $event[4], $event[5]);
            } else {
                $manager->registerEventHandler($event[0], $event[1], $event[2], $event[3], $event[4], $event[5]);
            }


        } // foreach($MODULE_EVENTS as $event)

        if(count($ex_events_res) > 0) {
            foreach($ex_events_res as $event) {
                $manager->unRegisterEventHandler($event[0], $event[1], $event[2], $event[3], $event[4]);
            }
        }

        if(static::CACHE_TIME && $cache->startDataCache()) {
            $cache->endDataCache($MODULE_EVENTS);
        }

        return $MODULE_EVENTS;
    }

    public static function lang(string $name, array $replace = []): string
    {
        $prefix = str_replace('.', '_', static::MODULE_ID);
        $str = Loc::getMessage($prefix . '_' . $name, $replace);
        return $str ?? 'Not found key ' . $prefix . '_' . $name;
    }
}