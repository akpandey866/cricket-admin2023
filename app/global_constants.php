<?php
/* Global constants for site */
define('FFMPEG_CONVERT_COMMAND', '');

define("ADMIN_FOLDER", "admin/");
define('DS', DIRECTORY_SEPARATOR);
define('ROOT', app()->basePath('public/'));
define('APP_PATH', app()->basePath('app'));


define("IMAGE_CONVERT_COMMAND", "");
define('WEBSITE_URL', url('/') . '/');
define('WEBSITE_JS_URL', WEBSITE_URL . 'js/');
define('WEBSITE_CSS_URL', WEBSITE_URL . 'css/');
define('WEBSITE_IMG_URL', WEBSITE_URL . 'img/');

define('WEBSITE_UPLOADS_ROOT_PATH', ROOT . DS . 'uploads' . DS);
define('WEBSITE_IMG_ROOT_PATH', ROOT . DS . 'img' . DS);
define('WEBSITE_UPLOADS_URL', WEBSITE_URL . 'uploads/');

define('ARTICLE_IMAGE_URL', WEBSITE_UPLOADS_URL . 'article/');
define('ARTICLE_IMAGE_ROOT_PATH', WEBSITE_UPLOADS_ROOT_PATH .  'article' . DS);

echo base_path();
die;
