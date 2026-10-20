-- Report the symptom the page shows, not a guessed root cause: "Error establishing
-- a database connection" can be a full disk, a stopped MySQL, a bad wp-config,
-- a PHP/plugin mismatch, or a corrupted database.
UPDATE `monitor_templates`
   SET `title` = 'Wordpress พัง - เว็บเปิดไม่ได้',
       `body`  = 'Domain : {domain}\nอาการ : {errorMsg}\nTime : {time}'
 WHERE `tpl_key` = 'wp_fatal';
