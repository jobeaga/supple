<?php

ini_set("session.gc_maxlifetime", 21600);
session_set_cookie_params(21600);
set_time_limit(0);

require_once('include/SuppleApplication.php');

SuppleApplication::parseTemplate('admin');

