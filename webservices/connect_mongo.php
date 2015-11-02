<?php
/**
 * @author Zyoruk
 * 
 * Connects to mongo at localhost.
 */
$connection = new MongoClient("localhost");
$db = $connection->odyssey_nosql;
?>