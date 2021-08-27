<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * A schedule task for scheduled allocation cron.
 *
 * @package   workshepallocation_scheduled
 * @copyright 2019 Simey Lameze <simey@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace workshepallocation_scheduled\task;

defined('MOODLE_INTERNAL') || die();

/**
 * The main schedule task for scheduled allocation cron.
 *
 * @package   workshepallocation_scheduled
 * @copyright 2019 Simey Lameze <simey@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class cron_task extends \core\task\scheduled_task {
    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('crontask', 'workshepallocation_scheduled');
    }

    /**
     * Run scheduled allocation cron.
     */
    public function execute() {
        global $CFG, $DB;

        $sql = "SELECT w.*
                  FROM {workshepallocation_scheduled} a
                  JOIN {workshep} w ON a.workshepid = w.id
                 WHERE a.enabled = 1
                   AND w.phase = 20
                   AND w.submissionend > 0
                   AND w.submissionend < ?
                   AND (a.timeallocated IS NULL OR a.timeallocated < w.submissionend)";
        $worksheps = $DB->get_records_sql($sql, array(time()));

        if (empty($worksheps)) {
            mtrace('... no worksheps awaiting scheduled allocation. ', '');
            return;
        }

        mtrace('... executing scheduled allocation in ' . count($worksheps) . ' workshep(s) ... ', '');

        require_once($CFG->dirroot . '/mod/workshep/locallib.php');

        foreach ($worksheps as $workshep) {
            $cm = get_coursemodule_from_instance('workshep', $workshep->id, $workshep->course, false, MUST_EXIST);
            $course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
            $workshep = new \workshep($workshep, $cm, $course);
            $allocator = $workshep->allocator_instance('scheduled');
            $allocator->execute();
        }
    }
}
