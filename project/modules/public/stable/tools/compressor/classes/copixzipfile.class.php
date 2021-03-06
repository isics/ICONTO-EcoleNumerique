<?php
/**
 * @package		tools
 * @subpackage	compressor
 * @author       Ferlet Patrice
 * @copyright    2001-2006 CopixTeam
 * @license 		http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 *
 * Inherits from zipfile; the zip.lib.php from phpMyAdmin
 * Its code is just copy / pasted here
 */

/**
 * Zip file creation class.
 * Makes zip files.
 *
 * Based on :
 *
 *  http://www.zend.com/codex.php?id=535&single=1
 *  By Eric Mueller <eric@themepark.com>
 *
 *  http://www.zend.com/codex.php?id=470&single=1
 *  by Denis125 <webmaster@atlant.ru>
 *
 *  a patch from Peter Listiak <mlady@users.sourceforge.net> for last modified
 *  date and time of the compressed file
 *
 * For Copix, filename changed from zip.lib.php to zip.class.php, this file
 * is from phpMyAdmin interface.
 *
 * Official ZIP file format: http://www.pkware.com/appnote.txt
 *
 * @package		tools
 * @subpackage	compressor
 */
class ZipFile
{
    /**
     * Array to store compressed data
     *
     * @var  array    $datasec
     */
    public $datasec      = array();

    /**
     * Central directory
     *
     * @var  array    $ctrl_dir
     */
    public $ctrl_dir     = array();

    /**
     * End of central directory record
     *
     * @var  string   $eof_ctrl_dir
     */
    public $eof_ctrl_dir = "\x50\x4b\x05\x06\x00\x00\x00\x00";

    /**
     * Last offset position
     *
     * @var  integer  $old_offset
     */
    public $old_offset   = 0;


    /**
     * Converts an Unix timestamp to a four byte DOS date and time format (date
     * in high two bytes, time in low two bytes allowing magnitude comparison).
     *
     * @param  integer  the current Unix timestamp
     *
     * @return integer  the current date in a four byte DOS format
     *
     * @access private
     */
    public function unix2DosTime($unixtime = 0)
    {
        $timearray = ($unixtime == 0) ? getdate() : getdate($unixtime);

        if ($timearray['year'] < 1980) {
            $timearray['year']    = 1980;
            $timearray['mon']     = 1;
            $timearray['mday']    = 1;
            $timearray['hours']   = 0;
            $timearray['minutes'] = 0;
            $timearray['seconds'] = 0;
        } // end if

        return (($timearray['year'] - 1980) << 25) | ($timearray['mon'] << 21) | ($timearray['mday'] << 16) |
        ($timearray['hours'] << 11) | ($timearray['minutes'] << 5) | ($timearray['seconds'] >> 1);
    }


    /**
     * Adds "file" to archive
     *
     * @param  string   file contents
     * @param  string   name of the file in the archive (may contains the path)
     * @param  integer  the current timestamp
     *
     * @access public
     */
    public function addFile($data, $name, $time = 0)
    {
        $name     = str_replace('\\', '/', $name);

        $dtime    = dechex($this->unix2DosTime($time));
        $hexdtime = '\x' . $dtime[6] . $dtime[7]
        . '\x' . $dtime[4] . $dtime[5]
        . '\x' . $dtime[2] . $dtime[3]
        . '\x' . $dtime[0] . $dtime[1];
        eval('$hexdtime = "' . $hexdtime . '";');

        $fr   = "\x50\x4b\x03\x04";
        $fr   .= "\x14\x00";            // ver needed to extract
        $fr   .= "\x00\x00";            // gen purpose bit flag
        $fr   .= "\x08\x00";            // compression method
        $fr   .= $hexdtime;             // last mod time and date

        // "local file header" segment
        $unc_len = strlen($data);
        $crc     = crc32($data);
        $zdata   = gzcompress($data);
        $zdata   = substr(substr($zdata, 0, strlen($zdata) - 4), 2); // fix crc bug
        $c_len   = strlen($zdata);
        $fr      .= pack('V', $crc);             // crc32
        $fr      .= pack('V', $c_len);           // compressed filesize
        $fr      .= pack('V', $unc_len);         // uncompressed filesize
        $fr      .= pack('v', strlen($name));    // length of filename
        $fr      .= pack('v', 0);                // extra field length
        $fr      .= $name;

        // "file data" segment
        $fr .= $zdata;

        // "data descriptor" segment (optional but necessary if archive is not
        // served as file)
        $fr .= pack('V', $crc);                 // crc32
        $fr .= pack('V', $c_len);               // compressed filesize
        $fr .= pack('V', $unc_len);             // uncompressed filesize

        // add this entry to array
        $this -> datasec[] = $fr;
        $new_offset        = strlen(implode('', $this->datasec));

        // now add to central directory record
        $cdrec = "\x50\x4b\x01\x02";
        $cdrec .= "\x00\x00";                // version made by
        $cdrec .= "\x14\x00";                // version needed to extract
        $cdrec .= "\x00\x00";                // gen purpose bit flag
        $cdrec .= "\x08\x00";                // compression method
        $cdrec .= $hexdtime;                 // last mod time & date
        $cdrec .= pack('V', $crc);           // crc32
        $cdrec .= pack('V', $c_len);         // compressed filesize
        $cdrec .= pack('V', $unc_len);       // uncompressed filesize
        $cdrec .= pack('v', strlen($name) ); // length of filename
        $cdrec .= pack('v', 0 );             // extra field length
        $cdrec .= pack('v', 0 );             // file comment length
        $cdrec .= pack('v', 0 );             // disk number start
        $cdrec .= pack('v', 0 );             // internal file attributes
        $cdrec .= pack('V', 32 );            // external file attributes - 'archive' bit set

        $cdrec .= pack('V', $this -> old_offset ); // relative offset of local header
        $this -> old_offset = $new_offset;

        $cdrec .= $name;

        // optional extra field, file comment goes here
        // save to central directory
        $this -> ctrl_dir[] = $cdrec;
    }


    /**
     * Dumps out file
     *
     * @return  string  the zipped file
     *
     * @access public
     */
    public function file()
    {
        $data    = implode('', $this -> datasec);
        $ctrldir = implode('', $this -> ctrl_dir);

        return
        $data .
        $ctrldir .
        $this -> eof_ctrl_dir .
        pack('v', sizeof($this -> ctrl_dir)) .  // total # of entries "on this disk"
        pack('v', sizeof($this -> ctrl_dir)) .  // total # of entries overall
        pack('V', strlen($ctrldir)) .           // size of central dir
        pack('V', strlen($data)) .              // offset to start of central dir
            "\x00\x00";                             // .zip file comment length
    }
}
/**
 * Classe de compression au format zip
 * @package		tools
 * @subpackage	compressor
 */
class CopixZipFile extends zipfile
{
    public function compressContent($content, $filename, $time=0)
    {
        $this->addFile($content,$filename,$time);
    }

    public function getBuffer()
    {
        return $this->file();
    }

    public function uncompress($filename,$path=null)
    {
        $zip = zip_open($filename);
        if ($zip) {
            $buf=array();
            while ($zip_entry = zip_read($zip)) {
                $entry=new _zip_entry();
                $entry->name     = zip_entry_name($zip_entry);
                $entry->realsize = zip_entry_filesize($zip_entry);
                $entry->size     = zip_entry_compressedsize($zip_entry);
                $entry->method    = zip_entry_compressionmethod($zip_entry);
                if (zip_entry_open($zip, $zip_entry, "r")) {
                    $entry->buffer = zip_entry_read($zip_entry, $entry->realsize);
                    zip_entry_close($zip_entry);
                    $buf[]=$entry;
                }
            }
            zip_close($zip);
        }

        //return the _zip_zntry struct if not path given to save to...
        if(!$path) return $buf;

        //else... uncompress to pathx
        foreach($buf as $file){
            $file->name = "/".$file->name;
            if (!CopixFile::write($path.$file->name,$file->buffer)) return false;
        }
        return true;
    }
}

/**
 * Représente une entrée d'une archive Zip
 * @package		tools
 * @subpackage	compressor
 */
class _zip_entry
{
    public $name;
    public $size;
    public $realsize;
    public $method;
    public $buffer;
}
