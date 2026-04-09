<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class DropStudentTables extends BaseCommand
{
    /**
     * The Command's Group
     *
     * @var string
     */
    protected $group = 'App';

    /**
     * The Command's Name
     *
     * @var string
     */
    protected $name = 'drop:studenttables';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Drops student-related tables to fix migration issues';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'drop:studenttables';

    /**
     * The Command's Arguments
     *
     * @var array
     */
    protected $arguments = [];

    /**
     * The Command's Options
     *
     * @var array
     */
    protected $options = [];

    /**
     * Actually execute a command.
     *
     * @param array $params
     */
    public function run(array $params)
    {
        $db = \Config\Database::connect();
        $db->query("DROP TABLE IF EXISTS travel_student_expense_items");
        $db->query("DROP TABLE IF EXISTS travel_student_members");
        $db->query("DROP TABLE IF EXISTS students");
        CLI::write("Tables dropped.", "green");
    }
}
