<?php
/**
 * Product classification for the upgrade/downgrade forms.
 *
 * Almost every conditional item on the downgrade form keys off what kind of
 * product is being cancelled, so this turns a Monday `product_group` string
 * into a list of tags.
 *
 * A list, not a single type: "AI + Local Starter Bundle" is a combined product
 * where the AI receptionist ships alongside a plan, so it must trigger both the
 * Araya flows and the plan-bundle keep-list.
 *
 * Resolution is map first, then keywords, then `addon`. The map is
 * authoritative because it is the only thing that can see a combined product:
 * of the 66 distinct product_group values in the mirror, the keyword rule
 * matches "AI Receptionist" or "Araya" in exactly one, and "AI + Local Starter
 * Bundle" is not it. A keyword rule is also invisible until it is wrong, which
 * is how getPlanType() silently broke the Solo-to-Bundle gate on the upgrade
 * form for every real product name.
 */

/**
 * The classification map from config.php, or [] when it is not configured.
 *
 * config.php is gitignored, so a deployment that has not copied the new key
 * across must still work: the keyword fallback covers the plan names, and the
 * only loss is the combined-product tagging.
 */
function upDownClassificationMap(): array
{
    static $map = null;
    if ($map !== null) {
        return $map;
    }

    $map  = [];
    $path = __DIR__ . '/config.php';
    if (is_file($path)) {
        $config = require $path;
        if (is_array($config) && isset($config['product_classification']) && is_array($config['product_classification'])) {
            $map = $config['product_classification'];
        }
    }

    return $map;
}

/**
 * Reduce a map entry to the canonical tag list, or [] if nothing valid is left.
 *
 * The map comes from config.php, which is gitignored and edited by hand at
 * deploy time, so it is untrusted input: a typo'd tag or a bad order is
 * realistic. This is the one place the canonical order lives. The JS twin
 * compares these arrays element by element, so order and membership are part
 * of the contract.
 *
 * `addon` means "triggers no conditional flow", so it is meaningless next to
 * any other tag and is dropped when one survives.
 */
function upDownNormalizeTags(array $tags): array
{
    $out = [];
    foreach (['araya', 'plan_bundle', 'plan_solo'] as $known) {
        if (in_array($known, $tags, true)) {
            $out[] = $known;
        }
    }

    if ($out === [] && in_array('addon', $tags, true)) {
        $out[] = 'addon';
    }

    return $out;
}

/**
 * Tags for one product_group string.
 *
 * @param string $productGroup The Monday product_group value, verbatim.
 * @param array  $map          Exact-match overrides. Defaults to config.php's.
 * @return string[] A non-empty subset of araya, plan_bundle, plan_solo, addon,
 *                  always in that order.
 */
function upDownClassifyProduct(string $productGroup, array $map = []): array
{
    $name = trim($productGroup);

    if ($map === []) {
        $map = upDownClassificationMap();
    }

    // Exact match wins outright: this is where a human corrects a
    // misclassification, so a keyword rule must not be able to add to it.
    // An entry that normalises to nothing (empty, or only unknown tags) falls
    // through to the keyword rules: a result is never empty.
    if (isset($map[$name]) && is_array($map[$name])) {
        $mapped = upDownNormalizeTags($map[$name]);
        if ($mapped !== []) {
            return $mapped;
        }
    }

    // Known limitation, deliberately deferred: these rules match across
    // comma-joined composite names, so "Local SEO Add-on, Direct Marketing
    // Bundle" infers plan_bundle from two different products. It changes the
    // outcome for none of the 66 real values.
    $hasLocal  = stripos($name, 'Local')  !== false;
    $hasBundle = stripos($name, 'Bundle') !== false;
    $hasSolo   = stripos($name, 'Solo')   !== false;

    $tags = [];

    if (stripos($name, 'AI Receptionist') !== false || stripos($name, 'Araya') !== false) {
        $tags[] = 'araya';
    }

    // "Local" is required alongside "Bundle" and "Solo" because both words are
    // used for add-on families: "Social Media Marketing Bundle" and "Mega
    // Marketing Solo" are add-ons, and firing the plan keep-list on them would
    // ask a customer cancelling a social media add-on what plan to keep.
    if ($hasLocal && $hasBundle) {
        $tags[] = 'plan_bundle';
    }

    if ($hasLocal && $hasSolo) {
        $tags[] = 'plan_solo';
    }

    // The safe default: an unrecognised product must not trigger a conditional
    // flow, so it gets the tag that triggers nothing.
    if ($tags === []) {
        $tags[] = 'addon';
    }

    return $tags;
}
