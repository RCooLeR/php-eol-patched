<?php
/* Files stay below 100 bytes; claimed reads stay below 256 bytes. */
foreach (array(false, true) as $subIfd) {
    foreach (array('size', 'offset') as $mode) {
        $entries = $subIfd ? 3 : 2;
        $thumbOffset = 8 + 2 + $entries * 12 + 4;
        $t = "II*\x00" . pack('V', 8) . pack('v', $entries);
        $t .= pack('vvVV', 0x0100, 4, 1, 1);
        $t .= pack('vvVV', 0x0101, 4, 1, 1);
        if ($subIfd) $t .= pack('vvVV', 0x014a, 4, 1, $thumbOffset);
        $t .= pack('V', $subIfd ? 0 : $thumbOffset);
        $t .= pack('v', 2);
        $t .= pack('vvVV', 0x0111, 4, 1, $mode === 'offset' ? 200 : 1);
        $t .= pack('vvVV', 0x0117, 4, 1, $mode === 'size' ? 128 : 1);
        $t .= pack('V', 0);
        $path = '/tmp/exif-small-' . (int)$subIfd . '-' . $mode . '.tif';
        file_put_contents($path, $t);
        $thumbnail = @exif_thumbnail($path);
        unlink($path);
        if ($thumbnail !== false) { fwrite(STDERR, "Expected no out-of-file thumbnail\n"); exit(1); }
    }
}
echo "PASS four bounded TIFF SUB_IFD/NEXT_IFD size/offset controls\n";
