<?php
/**
 * Created by PhpStorm.
 * User: rwachtler
 * Date: 23.01.16
 * Time: 15:15
 */

namespace AppBundle\Utils;


use AppBundle\EIconfig;
use Doctrine\Common\Proxy\Exception\InvalidArgumentException;

class EIcmsHelper
{
    /**
     * Generates random string (lowercase) with a defined length
     * @param $length - string length
     * @return string - generated string
     */
    public static function generateRandomString($length){
        $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
        $ret_string = '';
        for ($i = 0; $i < $length; $i++) {
            $ret_string .= $characters[mt_rand(0, strlen($characters) - 1)];
        }

        return $ret_string;
    }

    public static function unzipFile($sourceDir, $destinationDir){
        $zip = new \ZipArchive();

        if($zip->open($sourceDir)){
            $zip->extractTo($destinationDir);
            $zip->close();
            unlink($sourceDir);
            return true;
        }

        return false;
    }

    public static function performGetRequest($url){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $response  = curl_exec($ch);

        return json_decode($response);
    }

    public static function zipFolder($dir, $zipName){
        $rootPath = realpath($dir);
        $zipName .= ".zip";
        $zip = new \ZipArchive();
        $zip->open($dir.$zipName, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($rootPath),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $name => $file)
        {
            // Skip directories
            if (!$file->isDir())
            {
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($rootPath) + 1);

                // Add current file to archive
                $zip->addFile($filePath, $relativePath);
            }
        }

        $zip->close();

        return $zipName;
    }

    public static function removeDir($dir){
        if(! is_dir($dir)){
            throw new InvalidArgumentException("$dir must be a directory");
        }
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype($dir."/".$object) == "dir") EIcmsHelper::removeDir($dir."/".$object); else unlink($dir."/".$object);
                }
            }
            reset($objects);
            rmdir($dir);
        }
    }

    public static function createMySQLDump($destinationDir, $database){
        $dump = new MySQLDump(new \mysqli(EIconfig::$dbHost, EIconfig::$dbUser, EIconfig::$dbPass, $database));
        $dump->save($destinationDir.$database.".sql");
    }

    public static function deleteDatabase($dbName){

        $connection = new \mysqli(EIconfig::$dbHost, EIconfig::$dbUser, EIconfig::$dbPass);

        if($connection->connect_error) {
            die("Connection failed: " . $connection->connect_error);
        }

        $sql = "DROP DATABASE ".$dbName;
        if ($connection->query($sql) === TRUE) {
            $connection->close();
            return true;
        } else {
            echo "Error deleting database: " . $connection->error;
        }

        $connection->close();

        return false;

    }
}