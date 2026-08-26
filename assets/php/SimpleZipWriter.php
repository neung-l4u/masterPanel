<?php
/**
 * SimpleZipWriter
 *
 * Builds a ZIP archive in memory using the "store" method (no compression).
 * ZipArchive is used when the zip extension is loaded; otherwise the archive
 * is assembled by hand so the download keeps working on servers without it.
 *
 * The template JSON files are small, so skipping compression costs little.
 */
class SimpleZipWriter
{
    /** @var array<int,array{name:string,data:string}> */
    private $entries = array();

    public function addFile($name, $data)
    {
        $this->entries[] = array(
            'name' => str_replace('\\', '/', $name),
            'data' => (string)$data,
        );
    }

    public function count()
    {
        return count($this->entries);
    }

    /** @return string the raw ZIP bytes */
    public function build()
    {
        if (class_exists('ZipArchive')) {
            $bytes = $this->buildWithExtension();
            if ($bytes !== null) {
                return $bytes;
            }
        }

        return $this->buildManually();
    }

    private function buildWithExtension()
    {
        $tmp = tempnam(sys_get_temp_dir(), 'l4uzip');
        if ($tmp === false) {
            return null;
        }

        $zip = new ZipArchive();
        if ($zip->open($tmp, ZipArchive::OVERWRITE) !== true) {
            @unlink($tmp);
            return null;
        }

        foreach ($this->entries as $entry) {
            $zip->addFromString($entry['name'], $entry['data']);
        }
        $zip->close();

        $bytes = file_get_contents($tmp);
        @unlink($tmp);

        return $bytes === false ? null : $bytes;
    }

    /** Minimal ZIP writer: local headers + central directory, stored entries. */
    private function buildManually()
    {
        $local = '';
        $central = '';
        $offset = 0;
        list($dosTime, $dosDate) = $this->dosTimestamp();

        foreach ($this->entries as $entry) {
            $name = $entry['name'];
            $data = $entry['data'];
            $crc = crc32($data);
            $size = strlen($data);

            $header = "\x50\x4b\x03\x04"          // local file header signature
                . pack('v', 20)                   // version needed to extract
                . pack('v', 0)                    // general purpose flag
                . pack('v', 0)                    // compression method: store
                . pack('v', $dosTime)
                . pack('v', $dosDate)
                . pack('V', $crc)
                . pack('V', $size)                // compressed size
                . pack('V', $size)                // uncompressed size
                . pack('v', strlen($name))
                . pack('v', 0)                    // extra field length
                . $name;

            $local .= $header . $data;

            $central .= "\x50\x4b\x01\x02"        // central directory signature
                . pack('v', 20)                   // version made by
                . pack('v', 20)                   // version needed
                . pack('v', 0)
                . pack('v', 0)
                . pack('v', $dosTime)
                . pack('v', $dosDate)
                . pack('V', $crc)
                . pack('V', $size)
                . pack('V', $size)
                . pack('v', strlen($name))
                . pack('v', 0)                    // extra field length
                . pack('v', 0)                    // file comment length
                . pack('v', 0)                    // disk number start
                . pack('v', 0)                    // internal attributes
                . pack('V', 32)                   // external attributes
                . pack('V', $offset)
                . $name;

            $offset += strlen($header) + $size;
        }

        $end = "\x50\x4b\x05\x06"                 // end of central directory
            . pack('v', 0)
            . pack('v', 0)
            . pack('v', count($this->entries))
            . pack('v', count($this->entries))
            . pack('V', strlen($central))
            . pack('V', $offset)
            . pack('v', 0);                       // comment length

        return $local . $central . $end;
    }

    /** Current local time encoded as a DOS time/date pair. */
    private function dosTimestamp()
    {
        $now = getdate();
        $year = max(1980, $now['year']);

        $time = ($now['hours'] << 11) | ($now['minutes'] << 5) | (intdiv($now['seconds'], 2));
        $date = (($year - 1980) << 9) | ($now['mon'] << 5) | $now['mday'];

        return array($time, $date);
    }
}
