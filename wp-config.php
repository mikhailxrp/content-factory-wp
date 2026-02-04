<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', "mikhail700_cfs" );

/** Database username */
define( 'DB_USER', "mikhail700_cfs" );

/** Database password */
define( 'DB_PASSWORD', "!j*pdUncosr4" );

/** Database hostname */
define( 'DB_HOST', "localhost" );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'FihUQA~7f|NDG jZ.Cr]-5AZ!/q:x#pgl,BJ#>YxlId#/M*W6:]_xW,fkeS9KUB3' );
define( 'SECURE_AUTH_KEY',  '.x]Pp>x +|-07.o>gi52YZKrDt16]ju-7;^WUtbbF<FB^JmFt9@c!)FMHrd_ibdB' );
define( 'LOGGED_IN_KEY',    '*XJyV`@<!i*$T90[zlT}5oIjd9eE6c:<4b} fJ?J{kHz<wrz]A@DQ3{=6HXK)V+.' );
define( 'NONCE_KEY',        'Hd+Hu+;ha3]/+,xjJVbos7$RRI9}fv`O$-!QlbR;J{cJNX=[WxI[e;v:Mxj}pXap' );
define( 'AUTH_SALT',        't_Eh~_8WD,?OQ[S@ uc?x>d?N_K>*Nmh-2DhbI,{EIi/uHryf1Cx{&oXOLO|f<vi' );
define( 'SECURE_AUTH_SALT', '&5RbP9.`BAW61^Ya&!LXARmG~vWV8HqAQ<Qi`vCRCoo(]:(^2pz?FY)%,O;<fn*b' );
define( 'LOGGED_IN_SALT',   'MZ@vZ7T-*E.LgRTky0g)A g~&w}A7<#yl@S#GeO@1`,[pUA>5B`pQdS))9nW@c?Z' );
define( 'NONCE_SALT',       'cgQ495396*l;IOH))]&0e JwC&!Hb@[LZazzAV^E,4K$8kdSY3b!w:-~W8UBcn&2' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
