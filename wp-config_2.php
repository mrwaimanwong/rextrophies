<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'wordpress_4');

/** MySQL database username */
define('DB_USER', 'wordpress_2');

/** MySQL database password */
define('DB_PASSWORD', '*Zqck118');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'k3mp2wrbc7rlmdmwlepdotxetolihm917cvmdax3doxnzhfpx1wadhvakwhffwkf');
define('SECURE_AUTH_KEY',  'kbv9kb4z0zyni39pkk6d6vfeofhfwe2pkge6ugh44zi7xundc1rhcee9eziafhwu');
define('LOGGED_IN_KEY',    'j3jgg5jfuwia0kwngesu4tralhzsvs2688w08r0agex4clpt9mrsjer8jxbcztwg');
define('NONCE_KEY',        'u9k4xbdsolr6unvx5lerjdk1horbv6wiuuipopexhzt3rvjdcmymvcf8kf9orm5i');
define('AUTH_SALT',        '2eatfxwq6ajndz2wwtf1qz9q0egelqtrfp6urb9aysv38hmhpyahgz7q7wgqpld0');
define('SECURE_AUTH_SALT', 's2wt9k3juagyxbhdsoerg3tme8bla3qs7td3ihcxgjgtuvpeyzl73c1bmu3m1maf');
define('LOGGED_IN_SALT',   'rbivtpi9ysvqseavgnhbpegttong06ec7uxsnknwnpoqzptcete6thulzcx9wg8e');
define('NONCE_SALT',       'dk9sswx4h2zfxg6w07neil7p5mmob6iv3r5fdpwked7ukxulmruxrwxcoml1isns');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'uBA87uw4p_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');

/** Comment this out for production site **/
// define('FS_METHOD', 'direct');

/** Auto update Wordpress **/
define('WP_AUTO_UPDATE_CORE', true);
