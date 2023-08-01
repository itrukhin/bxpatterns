<?php
declare(strict_types=1);
namespace Dvk\Main\Helpers;

class SshCommand {

    public static function isExists($command): bool
    {
        return (bool) shell_exec("which " . escapeshellarg($command));
    }

    /**
     * @param $sourceFile
     * @param $destDir
     * @return false|string|null
     * @throws \Exception
     */
    public static function unzip(string $sourceFile, string $destDir) {

        if(self::isExists('unzip')) {
            $command = sprintf("unzip %s -d %s", escapeshellarg($sourceFile), escapeshellarg($destDir));
            return shell_exec($command);
        } else {
            throw new \Exception("Zip is not supported!");
        }
    }

    public static function unoconvCsv(string $sourceFile, string $destDir) {

        if(self::isExists('unoconv')) {
            $command = sprintf("unoconv -f csv -o %s %s", escapeshellarg($destDir), escapeshellarg($sourceFile));
            return shell_exec($command);
        } else {
            throw new \Exception("Unoconv is not supported!");
        }
    }

    public static function iconv1251(string $sourceFile) {

        if(self::isExists('iconv')) {
            $command = sprintf("iconv -f cp1251 -t utf-8 -o %s %s", escapeshellarg($sourceFile), escapeshellarg($sourceFile));
            return shell_exec($command);
        } else {
            throw new \Exception("Iconv is not supported!");
        }
    }
}
