<?php

namespace Config;

/**
 * Paths
 *
 * Holds the paths that are used by the system to
 * locate the main directories, app, system, etc.
 *
 * Modified for cPanel deployment at perjadin.polsri.ac.id
 * All paths use absolute paths to /home/polsripayop/perjadin-app/
 */
class Paths
{
    /**
     * ---------------------------------------------------------------
     * SYSTEM FOLDER NAME
     * ---------------------------------------------------------------
     */
    public string $systemDirectory = '/home/polsripayop/perjadin-app/vendor/codeigniter4/framework/system';

    /**
     * ---------------------------------------------------------------
     * APPLICATION FOLDER NAME
     * ---------------------------------------------------------------
     */
    public string $appDirectory = '/home/polsripayop/perjadin-app/app';

    /**
     * ---------------------------------------------------------------
     * WRITABLE DIRECTORY NAME
     * ---------------------------------------------------------------
     */
    public string $writableDirectory = '/home/polsripayop/perjadin-app/writable';

    /**
     * ---------------------------------------------------------------
     * TESTS DIRECTORY NAME
     * ---------------------------------------------------------------
     */
    public string $testsDirectory = '/home/polsripayop/perjadin-app/tests';

    /**
     * ---------------------------------------------------------------
     * VIEW DIRECTORY NAME
     * ---------------------------------------------------------------
     */
    public string $viewDirectory = '/home/polsripayop/perjadin-app/app/Views';
}
