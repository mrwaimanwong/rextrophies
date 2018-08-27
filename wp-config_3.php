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
define('DB_NAME', 'wp589');

/** MySQL database username */
define('DB_USER', 'wp589');

/** MySQL database password */
define('DB_PASSWORD', '4dSp[8-3nB');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

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
define('AUTH_KEY',         'qrfkr5l7vlqtllgx0x2t7kd404t9l7ehgfwuxnhjjujypjj6nnruqyoeq28ujgkr');
define('SECURE_AUTH_KEY',  'j2rxpbkrmgqu0vzlqdi9kp7ltsqagf7hal0z7la8rfommwvrxneaktj5eobbrip9');
define('LOGGED_IN_KEY',    '247xvdhfxelr2yoahfjuecehwnvxmytiq6dsdd817zmilntrtilam5rmq5b1ckmc');
define('NONCE_KEY',        'dunods69n1kqixmn0nay9yvkw8sexe8mtubnlvw0rplrvqndvfnfxrqj7jw6wmur');
define('AUTH_SALT',        'xpytxnb1ltbgzbiccrdk5oijryzxdhjp8oqgczpw8w2x9tfwiwrzka3mkfavzxlg');
define('SECURE_AUTH_SALT', 'a1kxuw4ouqngquujwsnskh8p64f05yt8ekqlxtsoylda16ifrvjaqagueneegz0g');
define('LOGGED_IN_SALT',   'kii5xnx8zg6tst62qynl15jitrdzj1briksymbjorkdqggoxlgiek7tkt383vncq');
define('NONCE_SALT',       'uouhavreccxu7jbzirybz3n36c9dkp4fdcc8kffofievgm8tnxsulfg5qpft35lj');

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
define( 'WP_MEMORY_LIMIT', '256M' );

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
