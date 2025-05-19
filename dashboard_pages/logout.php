<?php
session_unset();
session_destroy();
header("Location: /_Book_Store_/login_form");
exit;
