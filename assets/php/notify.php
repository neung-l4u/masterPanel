<?php
/**
 * Team notification helper.
 *
 * Writes a row that everyone on a team sees in the navbar bell. Read state is
 * per-person (notification_reads), so one row serves the whole team.
 *
 * The (type, refID) pair is UNIQUE, so calling this twice for the same event
 * is a no-op rather than a duplicate alert.
 */

if (!function_exists('notifyTeam')) {
    /**
     * @param int         $teamID  Team to alert (5 = IT).
     * @param string      $type    Machine key, e.g. 'signup_no_stripe'.
     * @param string      $title   Short headline.
     * @param string|null $message Detail line.
     * @param string|null $link    Page to open, e.g. 'main.php?p=viewLogs'.
     * @param int|null    $refID   Source row id, used to de-duplicate.
     * @return bool True if a new alert was created; false if it already existed.
     */
    function notifyTeam($teamID, $type, $title, $message = null, $link = null, $refID = null) {
        global $db;
        if (empty($db)) return false;

        try {
            // INSERT IGNORE leans on the unique (type, refID) key so a repeated
            // save of the same signup does not raise the alert twice.
            $db->query(
                'INSERT IGNORE INTO `notifications`
                 (`teamID`, `type`, `title`, `message`, `link`, `refID`, `createAt`)
                 VALUES (?,?,?,?,?,?,?)',
                (int)$teamID, $type, $title, $message, $link, $refID, date('Y-m-d H:i:s')
            );

            // IGNORE means a duplicate affects no rows; report that honestly so
            // callers can tell a new alert from one that already existed.
            return $db->affectedRows() > 0;
        } catch (\Throwable $e) {
            // Never let a failed notification break the flow that triggered it.
            error_log('notifyTeam failed: ' . $e->getMessage());
            return false;
        }
    }
}
