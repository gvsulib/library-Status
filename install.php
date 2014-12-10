<?php
if(!@copy('https://github.com/gvsulib/library-Status/archive/master.zip','./somefile.zip'))
{
    $errors= error_get_last();
    echo "COPY ERROR: ".$errors['type'];
    echo "<br />\n".$errors['message'];
} else {
    echo "File copied from remote!";
}
?>