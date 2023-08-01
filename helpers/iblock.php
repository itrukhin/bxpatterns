<?php
namespace Dvk\Main\Helpers;

use Bitrix\Iblock\ElementTable;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Iblock\SectionTable;
use Bitrix\Main\Data\Cache;
use Bitrix\Main\Loader;

/**
 * @package Main\Helpers
 */
class Iblock {

    const CACHE_DIR = 'dvk/ib_props';
    const CACHE_TIME = 86400;

    public static function setElementSection($elementId, $sectionId) {

        $res = ElementTable::update($elementId, ['IBLOCK_SECTION_ID' => $sectionId]);
        return $res->isSuccess();
    }

    public static function getPropCodes($iblockId) {

        $cache = Cache::createInstance();
        if($cache->initCache(self::CACHE_TIME, "ib_prop_codes_" . $iblockId, self::CACHE_DIR)) {
            return $cache->getVars();
        } else {
            $ibProps = [];
            $propRows = PropertyTable::getList([
                'select' => ['ID', 'CODE'],
                'filter' => ['IBLOCK_ID' => $iblockId],
            ])->fetchAll();

            foreach($propRows as $propRow) {
                $propId = $propRow['ID'];
                $ibProps[$propId] = $propRow['CODE'];
            }
            if ($cache->startDataCache()) {
                $cache->endDataCache($ibProps);
            }
            return $ibProps;
        }
    }

    public static function getProps($iblockId) {

        $cache = Cache::createInstance();
        if($cache->initCache(self::CACHE_TIME, "ib_prop_" . $iblockId, self::CACHE_DIR)) {
            return $cache->getVars();
        } else {
            $ibProps = [];
            $propRows = PropertyTable::getList([
                'filter' => ['IBLOCK_ID' => $iblockId],
            ])->fetchAll();

            foreach($propRows as $propRow) {
                $propCode = $propRow['CODE'];
                $ibProps[$propCode] = $propRow;
            }
            if ($cache->startDataCache()) {
                $cache->endDataCache($ibProps);
            }
            return $ibProps;
        }
    }

    public static function getPropByCode($iblockId, $code) {

        $props = self::getProps($iblockId);
        return $props[$code];
    }

    public static function extractElementProps($element, $iblockId) {

        $elements = self::extractElementsProps([0 => $element], $iblockId);
        return $elements[0];
    }

    public static function extractElementsProps($elements, $iblockId) {

        $ibProps = self::getPropCodes($iblockId);

        foreach($elements as $i => $element) {
            foreach($ibProps as $propId => $propCode) {
                $key = "PROPERTY_" . $propId;
                if(array_key_exists($key, $element)) {
                    $elements[$i]['PROPS'][$propCode] = $element[$key];
                    unset($elements[$i][$key]);
                }
            }
        }

        return $elements;
    }

    public static function getElementDataById($iblockId, $elementId) {

        $selectFields = [
            'ID',
            'IBLOCK_ID',
            'XML_ID',
            'NAME',
            'PROPERTY_*'
        ];
        Loader::includeModule('iblock');
        $res = \CIBlockElement::GetList(
            false,
            ['IBLOCK_ID' => $iblockId, 'ID' => $elementId],
            false,
            false,
            $selectFields
        );
        if($el = $res->GetNextElement(false, false)) {
            $data = $el->GetFields();
            $props = $el->GetProperties();
            foreach($props as $code => $prop) {
                $data[$code] = $prop['VALUE'];
            }
            return $data;
        }
        return null;
    }

    public static function getElementIdByXmlId($iblockId, $xmlId) {

        $filter = array('=XML_ID' => (string) $xmlId);
        if($iblockId) {
            $filter['IBLOCK_ID'] = $iblockId;
        }

        $res = ElementTable::getList(array(
            'select' => array('ID'),
            'filter' => $filter,
        ))->fetchAll();

        if(is_array($res) && count($res) == 1) {
            return $res[0]['ID'];
        }
        return null;
    }

    public static function getElementSectionIdByBxId($elementId) {

        $res = ElementTable::getList(array(
            'select' => array('IBLOCK_SECTION_ID'),
            'filter' => array('ID' => (int) $elementId),
        ))->fetchAll();

        if(is_array($res) && count($res) > 0) {
            return (int) $res[0]['IBLOCK_SECTION_ID'];
        }
        return null;
    }

    public static function getElementXmlIdByBxId($elementId) {

        $res = ElementTable::getList(array(
            'select' => array('XML_ID'),
            'filter' => array('ID' => (int) $elementId),
        ))->fetchAll();

        if(is_array($res) && count($res) > 0) {
            return $res[0]['XML_ID'];
        }
        return null;
    }

    public static function getiblockIdByElementId($elementId) {

        $res = ElementTable::getList(array(
            'select' => ['IBLOCK_ID'],
            'filter' => ['ID' => (int) $elementId],
        ))->fetchAll();

        if(is_array($res) && count($res) > 0) {
            return $res[0]['IBLOCK_ID'];
        }
        return null;
    }

    public static function getSectionIdByXmlId($iblockId, $xmlId) {

        $res = SectionTable::getList(array(
            'select' => array('ID'),
            'filter' => array('IBLOCK_ID' => $iblockId, 'XML_ID' => trim((string) $xmlId)),
            'limit' => 1,
            'order' => array('LEFT_MARGIN' => 'ASC'),
        ))->fetchAll();

        if(is_array($res) && count($res) == 1) {
            return $res[0]['ID'];
        }
        return null;
    }

    public static function getXmlIdByBxSectionId($sectionId) {

        $res = SectionTable::getList(array(
            'select' => array('XML_ID'),
            'filter' => array('ID' => (int) $sectionId),
        ))->fetchAll();

        if(is_array($res) && count($res) > 0) {
            return $res[0]['XML_ID'];
        }
        return null;
    }

    public static function getSectionNameById($sectionId) {

        $res = SectionTable::getList(array(
            'select' => array('NAME'),
            'filter' => array('ID' => (int) $sectionId),
        ))->fetchAll();

        if(is_array($res) && count($res) > 0) {
            return $res[0]['NAME'];
        }
        return null;
    }

    public static function getSectionCodeById($sectionId) {

        $res = SectionTable::getList(array(
            'select' => array('CODE'),
            'filter' => array('ID' => (int) $sectionId),
        ))->fetchAll();

        if(is_array($res) && count($res) > 0) {
            return $res[0]['CODE'];
        }
        return null;
    }

    public static function getiblockIdBySectionId($sectionId) {

        $res = SectionTable::getList(array(
            'select' => ['IBLOCK_ID'],
            'filter' => ['ID' => (int) $sectionId],
        ))->fetchAll();

        if(is_array($res) && count($res) > 0) {
            return $res[0]['IBLOCK_ID'];
        }
        return null;
    }

    public static function getSectionByXmlId($iblockId, $xmlId) {

        return SectionTable::getRow([
            'filter' => array('IBLOCK_ID' => $iblockId, 'XML_ID' => $xmlId),
            'order' => array('ID' => 'ASC'),
        ]);
    }

    public static function getSectionById($id, $select = ['*']) {

        $row = SectionTable::getByPrimary($id, ['select' => $select])->fetchAll();
        if(is_array($row)) {
            return $row[0];
        }
        return null;
    }

    public static function getSectionByCode($iblockId, $code) {

        $res = SectionTable::getList(array(
            'filter' => array('IBLOCK_ID' => $iblockId, 'CODE' => trim((string) $code)),
            'order' => array('LEFT_MARGIN' => 'ASC'),
        ))->fetchAll();

        if(is_array($res) && count($res) == 1) {
            return $res[0];
        }
        return null;
    }

    public static function getSectionsChain($id, $select = ['ID', 'CODE', 'NAME']) {

        $select[] = 'LEFT_MARGIN';
        $select[] = 'RIGHT_MARGIN';
        $select[] = 'IBLOCK_ID';
        $select = array_unique($select);

        $section = self::getSectionById($id, $select);

        $chain = [];
        if(is_array($section)) {
            $chainRes = SectionTable::getList([
                'select' => $select,
                'filter' => [
                    'IBLOCK_ID' => $section['IBLOCK_ID'],
                    '<LEFT_MARGIN' => $section['LEFT_MARGIN'],
                    '>RIGHT_MARGIN' => $section['RIGHT_MARGIN'],
                ],
                'order' => [
                    'LEFT_MARGIN' => 'ASC'
                ],
            ])->fetchAll();
            if(is_array($chainRes)) {
                foreach($chainRes as $row) {
                    $chain[] = $row;
                }
            }
            $chain[] = $section;
        }
        return $chain;
    }
}
