<?php

if (!getenv('BARBERRY')) {
    echo "Please define running Barberry instance location like BARBERRY=http://barberry.host \n";
    echo "ex: BARBERRY=http://barberry.host ./test/integration/run.sh \n";
    exit (1);
}

require __DIR__ . '/../../vendor/autoload.php';
