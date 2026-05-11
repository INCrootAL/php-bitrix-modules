<?php

if (is_file($_SERVER["DOCUMENT_ROOT"] . "/local/modules/incrootal.migration/admin/sprint_migrations.php")) {
    require($_SERVER["DOCUMENT_ROOT"] . "/local/modules/incrootal.migration/admin/sprint_migrations.php");
} else {
    require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/incrootal.migration/admin/sprint_migrations.php");
}
