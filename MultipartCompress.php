<?php

/* 
 * Copyright (C) 2014 Everton
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * This PHP class compact and split files and merge and decompact files.
 */
class MultipartCompress{
    
    /**
     * Compact a file in multipart zip archive.
     * @param string $i The file to compact.
     * @param string $o The zip archive (*.zip).
     * @param integer $s The mnax size (in byte) for the parts. 0 to no parts.
     * @return boolean Return number of parts created.
     */
    public static function zip($i, $o, $s = 0){
        
        $zp = new ZipArchive();
        
        if(file_exists($o)){
            $flags = 0;
        }else{
            $flags = ZipArchive::CREATE;
        }
        
        $zp->open($o, $flags);
        
        $success = $zp->addFile($i, basename($i));
        
        $zp->close();
        
        if($success){
            if($s > 0){
                return self::split($o, $s);
            }
        }else{
            return false;
        }
        
        
    }
    
    /**
     * Split the zip archive.
     * @param string $i The zip archive.
     * @param integer $s The max size for the parts.
     * @return integer Return the number of parts created.
     */
    protected static function split($i, $s){
        $fs = filesize($i);
        //$bn = basename($i);
        //$dn = dirname($i).DIRECTORY_SEPARATOR;
        $p = 1;
        
        for($c = 0; $c < $fs; $c = $c + $s){
            $data = file_get_contents($i, FILE_BINARY, null, $c, $s);
            //$fn = "$dn$bn.$p";
            $fn = "$i.$p";
            file_put_contents($fn, $data);
            $p++;
            unset($data);
        }
        unlink($i);
        return $p - 1;
    }
    
    /**
     * Decompact the zip archive.
     * @param string $i The zip archive (*.zip).
     * @param string $o The directory name for extract.
     * @param integer $p Number of parts of the zip archive.
     * @return boolean Return TRUE for success or FALSE for fail.
     */
    public static function unzip($i, $o, $p = 0){
        $success = true;
        if($p > 0){
            $success = self::merge($i, $p);
        }
        if($success == false){
            return false;
        }
        
        $zp = new ZipArchive();
        $zp->open($i);
        if($zp->extractTo($o)){
            $zp->close();
            unset($zp);
            unlink($i);
            return true;
        }else{
            return false;
        }
        
    }
    
    /**
     * Merge the parts of zip archive.
     * @param string $i The zip archive (*.zip).
     * @param integer $p Number of parts of the zip archive.
     * @return boolean Return TRUE for success or FALSE for fail.
     */
    protected static function merge($i, $p){
        for($c = 1; $c <= $p; $c++){
            $data = file_get_contents("$i.$c");
            file_put_contents($i, $data, FILE_APPEND);
            unset($data);
        }
        return true;
    }
}