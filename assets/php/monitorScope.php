<?php
/**
 * Which websiteList statuses the monitor watches.
 *
 * Live        — normal client site
 * Transferred — moved from Doodar to WordPress; still a client, same as Live
 * Subdomain   — sites we host under our own domains
 *
 * Anything else (Unpublished, Draft, Redirect, Pre Live) is not ours to watch.
 * Import, pause and resume all read this list, so a new status only has to be
 * added here.
 */
const MONITOR_STATUSES = ['Live', 'Transferred', 'Subdomain'];

/** Comma-separated quoted list for use inside an SQL IN (...) clause. */
function monitorStatusSql(): string {
    return "'" . implode("','", MONITOR_STATUSES) . "'";
}
