--TEST--
INSERT or UPDATE
--FILE--
<?php
include_once dirname(__FILE__) . '/connect.inc.php';

for ($i = 0; $i < 2; $i++) {
    echo $software->application()->insert_update(['id' => 5], ['author_id' => 12, 'title' => 'Texy', 'web' => '', 'slogan' => "$i"]) . "\n";
}
$application = $software->application[5];
echo $application->application_tag()->insert_update(['tag_id' => 21], []) . "\n";
$software->application('id', 5)->delete();
?>
--EXPECTF--
1
2
1
