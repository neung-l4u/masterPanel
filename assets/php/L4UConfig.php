<?php
/**
 * Reader for the l4uConfig table.
 *
 * Licence keys and storage credentials are kept in the database rather than
 * in the repository, so the code can ship without them and each environment
 * carries its own. Anything missing simply comes back blank, and the caller
 * skips the step that needed it.
 */

class L4UConfig {

    private static $cache = null;

    /**
     * Read every configured value, once per request.
     * @param object $db
     * @return array key => value
     */
    public static function all($db) {
        if (self::$cache !== null) {
            return self::$cache;
        }

        self::$cache = [];

        // A missing table is not an error here: it only means nothing has
        // been configured yet on this environment
        $rows = $db->query('SELECT cfKey, cfValue FROM l4uConfig;')->fetchAll();

        if (is_array($rows)) {
            foreach ($rows as $row) {
                self::$cache[$row['cfKey']] = $row['cfValue'];
            }
        }

        return self::$cache;
    }

    /**
     * One configured value.
     * @param object $db
     * @param string $key
     * @param string $default
     * @return string
     */
    public static function get($db, $key, $default = '') {
        $all = self::all($db);

        return isset($all[$key]) && $all[$key] !== '' ? $all[$key] : $default;
    }

    /**
     * Forget the cached values, so a later read sees fresh ones.
     * @return void
     */
    public static function flush() {
        self::$cache = null;
    }
}
