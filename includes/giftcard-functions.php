<?php


/**
 * Get Giftcard
 *
 * Retrieves a complete giftcard code by giftcard ID.
 *
 * @param string $giftcard_id Giftcard ID
 * @return array
 * @deprecated
 */
function wcgc_get_giftcard($giftcard_id)
{
    $giftcard = get_post($giftcard_id);

    if (get_post_type($giftcard_id) != WCGC()->post_type) {
        return false;
    }

    return $giftcard;
}

/**
 * @deprecated
 */
function wcgc_get_giftcard_by_code($value = '')
{
    global $wpdb;

    // Check for Giftcard
    $giftcard_found = $wpdb->get_var($wpdb->prepare("
		SELECT $wpdb->posts.ID
		FROM $wpdb->posts
		WHERE $wpdb->posts.post_type = '" . WCGC()->post_type . "'
		AND $wpdb->posts.post_status = 'publish'
		AND $wpdb->posts.post_title = '%s'
	", $value));

    return $giftcard_found;
}
