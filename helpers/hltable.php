<?php
namespace Dvk\Main\Helpers;

use Bitrix\Highloadblock\DataManager;
use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\DB\Exception;
use Bitrix\Main\Loader;

class HlTable {

    public static function getTableName(string $hlName) {

        if(!Loader::includeModule('highloadblock')) {
            throw new Exception('Module HighLoadBlock not found!');
        }

        if(is_numeric($hlName)) {
            $hlBlock = HighloadBlockTable::getRow([
                'filter' => ['ID' => $hlName]
            ]);
        } else {
            $hlBlock = HighloadBlockTable::getRow([
                'filter' => ['NAME' => $hlName]
            ]);
        }

        if(is_array($hlBlock)) {
            return $hlBlock['TABLE_NAME'];
        } else {
            throw new \Exception('HighLoadBlock ' . $hlName . ' not found!');
        }
    }

    /**
     * @param string $hlName - HlName or ID
     * @return DataManager
     */
    public static function getInstance(string $hlName) {

        if(!Loader::includeModule('highloadblock')) {
            throw new Exception('Module HighLoadBlock not found!');
        }

        if(is_numeric($hlName)) {
            $hlBlock = HighloadBlockTable::getRow([
                'filter' => ['ID' => $hlName]
            ]);
        } else {
            $hlBlock = HighloadBlockTable::getRow([
                'filter' => ['NAME' => $hlName]
            ]);
        }

        if(is_array($hlBlock)) {

            $entity = HighloadBlockTable::compileEntity($hlBlock);
            $entity_class = $entity->getDataClass();

            /* @var $table DataManager */
            $table =  new $entity_class();
            return $table;

        } else {
            throw new \Exception('HighLoadBlock ' . $hlName . ' not found!');
        }
    }

    public static function getHlIdByName($hlName) {

        if(!Loader::includeModule('highloadblock')) {
            throw new Exception('Module HighLoadBlock not found!');
        }

        $hlBlock = HighloadBlockTable::getRow([
            'select' => ['ID'],
            'filter' => ['NAME' => $hlName]
        ]);

        if(is_array($hlBlock)) {
            return (int) $hlBlock['ID'];
        }

        return null;
    }
}