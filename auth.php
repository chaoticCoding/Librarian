 <?php
include_once "core/includes/mcrypt.static";
/**
 * Created by PhpStorm.
 * User: shawn
 * Date: 10/27/14
 * Time: 3:45 PM
 */


print(urlencode(mcrypt::Encrypt("hello")));


if($_GET['decrypt']){
	print(mcrypt::Decrypt($_GET['decrypt']));
}