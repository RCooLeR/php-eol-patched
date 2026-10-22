<?php
$source = file_get_contents('/usr/src/php/ext/exif/exif.c');
$start = strpos($source, 'static bool exif_thumbnail_in_file(image_info_type *ImageInfo)');
if ($start === false) exit(2);
$end = strpos($source, '{', $start) + 1;
$depth = 1;
while ($depth && $end < strlen($source)) {
    if ($source[$end] === '{') $depth++;
    if ($source[$end] === '}') $depth--;
    $end++;
}
if ($depth) exit(2);
$template = file_get_contents('/tests/native-exif-bound.c.in');
file_put_contents('/tmp/native-exif-bound.c', str_replace('@NATIVE_EXIF_BOUND@', substr($source, $start, $end - $start), $template));
