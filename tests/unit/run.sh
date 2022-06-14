dir=$(cd "$(dirname "$0")";pwd);

cd $dir
../../vendor/bin/phpunit . $*
