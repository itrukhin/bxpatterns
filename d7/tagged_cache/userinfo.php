<?php
declare(strict_types=1);
namespace Dvk\Main\Data;

use Bitrix\Main\Application;
use Bitrix\Main\Data\Cache;

class UserInfo {

    const CACHE_KEY = 'userInfo';
    const CACHE_DIR = '/dvk.main/user_info/';
    const CACHE_TIME = 86400;

    const USER_INFO_IBLOCK_ID = 211;
    const SELECT_FIELDS = [
        'ID',
        'IBLOCK_ID',
        'XML_ID',
        'NAME',
        'PROPERTY_*'
    ];

    private Cache $cache;
    private array $userInfo;

    public function __construct()
    {
        $this->cache = Cache::createInstance();
        $taggedCache = Application::getInstance()->getTaggedCache();

        if($this->cache->initCache(self::CACHE_TIME, self::CACHE_KEY, self::CACHE_DIR)) {
            $this->userInfo = $this->cache->getVars();
        } else {
            $res = \CIBlockElement::GetList(
                false,
                ['IBLOCK_ID' => self::USER_INFO_IBLOCK_ID],
                false,
                false,
                self::SELECT_FIELDS
            );
            while($el = $res->GetNextElement(false, false)) {
                $fields = $el->GetFields();
                $props = $el->GetProperties();
                $userId = (int) $fields['NAME'];
                $userInfo = [
                    'ID' => (int) $fields['ID'],
                    'XML_ID' => $fields['XML_ID'],
                ];
                foreach($props as $code => $prop) {
                    $userInfo[$code] = $prop['VALUE'];
                }
                $this->userInfo[$userId] = $userInfo;
            }

            if($this->cache->startDataCache()) {

                $taggedCache->startTagCache(self::CACHE_DIR);
                // сбрасывать кеш при изменении данных инфоблока
                $taggedCache->registerTag('iblock_id_' . self::USER_INFO_IBLOCK_ID);

                // Если что-то пошло не так и решили кеш не записывать
                $cacheInvalid = false;
                if ($cacheInvalid) {
                    $taggedCache->abortTagCache();
                    $this->cache->abortDataCache();
                }

                $taggedCache->endTagCache();
                $this->cache->endDataCache($this->userInfo);
            }
        }
    }

    public function getByUserId(int $userId): array
    {
        if(is_array($this->userInfo[$userId])) {
            return $this->userInfo[$userId];
        }
        return [];
    }

    public function cleanCache()
    {
        $this->cache->cleanDir(self::CACHE_DIR);
    }
}