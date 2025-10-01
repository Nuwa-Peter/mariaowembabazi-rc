<?php
// config.php

/**
 * Defines the base path for the application.
 * This constant is used to construct absolute paths for browser-facing resources
 * like images, CSS, and JavaScript files. Using a constant ensures that all paths
 * are consistent and that the application remains portable across different
 * server environments or subdirectory structures.
 *
 * Example:
 * If the application is hosted at http://localhost/v19/, set BASE_PATH to '/v19'.
 * If the application is hosted at the root (http://localhost/), set BASE_PATH to an empty string ''.
 */
define('BASE_PATH', '/v19');

/**
 * --- Server & Deployment Configuration Notes ---
 *
 * Base URL:
 * The application is configured to run from the subdirectory specified in BASE_PATH.
 * The full base URL for this setup is: http://localhost/v19/
 *
 * File Permissions (Troubleshooting 403 Forbidden Errors):
 * If images or other assets are not loading and you see a "403 Forbidden" error,
 * it is likely a file system permissions issue. The web server (e.g., Apache) needs
 * permission to read the files.
 *
 * From your project's root directory on the server, you may need to run the following
 * commands in your terminal:
 *
 * To make the images directory accessible:
 * chmod 755 images
 *
 * To make the logo file readable:
 * chmod 644 images/logo.png
 *
 */
?>