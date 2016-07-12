<?php
if (!getenv('BARBERRY')) {
    echo "Please define running Barberry instance hostname like BARBERRY=barberry.host \n";
    echo "ex: BARBERRY=barberry.host ./test/integration/run.sh \n";
    exit (1);
}

require __DIR__ . '/../../vendor/autoload.php';
