<?php
session_unset();
session_destroy();
header("Location: /_Book_Store_/landing_page");
exit;
