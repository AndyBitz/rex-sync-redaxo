<?php

echo rex_view::title('Rex-Sync');
$subpage = rex_be_controller::getCurrentPagePart(1);
include rex_be_controller::getCurrentPageObject()->getSubPath();

?>
