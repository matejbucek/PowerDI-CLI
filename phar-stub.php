#!/usr/bin/env php
<?php

Phar::mapPhar('powerdi.phar');
require 'phar://powerdi.phar/vendor/autoload.php';
require 'phar://powerdi.phar/src/PowerDI.php';
__HALT_COMPILER();