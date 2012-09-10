<?
include('include/config.inc.php');
DBConn::getInstance('');
print_r(DBConn::$memcached->getStats());
?>