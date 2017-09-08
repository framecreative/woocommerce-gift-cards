<?php

/**
 * Class WC_Gift_Card
 */
class WC_Gift_Card
{

    /** @public int (post) ID */
    public $id;

    /** @var $post WP_Post */
    public $post = null;


    public $number;
    public $to_name;
    public $to_email;
    public $from_name;
    public $from_email;
    public $note;
    public $amount;
    public $balance;
    public $expiry_date;
    public $is_email_sent;
    public $exists = false;

    /**
     * @param $id
     *
     * @return bool
     */
    public function get_by_post_id($id)
    {
        return $this->init(get_post($id));
    }

    /**
     * @param $number
     *
     * @return bool
     */
    public function get_by_number($number)
    {
        $post = get_page_by_title($number, 'OBJECT', WCGC()->post_type);
        return $this->init($post);
    }

    /**
     * @param $post
     * @return bool
     */
    public function init($post)
    {
        if (! $post) {
            return false;
        }

        if ($post->post_type != WCGC()->post_type) {
            return false;
        }

        // Populate object
        $this->id = $post->ID;
        $this->number = $post->post_title;
        $this->exists = true;

        return true;
    }

    /**
     *
     */
    public function exists()
    {
        return $this->exists;
    }

    /**
     * @return mixed
     */
    public function get_id()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function get_number()
    {
        return $this->number;
    }

    /**
     * Retrieve the gift card to email
     * @return string
     */
    public function get_to_email()
    {
        if ($this->to_email) {
            return $this->to_email;
        }

        $this->to_email = get_post_meta($this->id, 'wcgc_email_to', true);
        return apply_filters(WCGC()->plugin_prefix . 'get_gift_card_to_email', $this->to_email, $this->id);
    }

    /**
     * Retrieve the gift card to email
     * @return string
     */
    public function get_to_name()
    {
        if ($this->to_name) {
            return $this->to_emto_nameail;
        }

        $this->to_name = get_post_meta($this->id, 'wcgc_to', true);
        return apply_filters(WCGC()->plugin_prefix . 'get_gift_card_to_name', $this->to_name, $this->id);
    }

    /**
     * Retrieve the gift card to email
     * @return string
     */
    public function get_from_email()
    {
        if ($this->from_email) {
            return $this->from_email;
        }

        $this->from_email = get_post_meta($this->id, 'wcgc_email_from', true);
        return apply_filters(WCGC()->plugin_prefix . 'get_gift_card_from_email', $this->from_email, $this->id);
    }

    /**
     * Retrieve the gift card to email
     * @return string
     */
    public function get_from_name()
    {
        if ($this->from_name) {
            return $this->from_name;
        }

        $this->from_name = get_post_meta($this->id, 'wcgc_from', true);
        return apply_filters(WCGC()->plugin_prefix . 'get_gift_card_from_name', $this->from_name, $this->id);
    }

    /**
     * Retrieve the gift card to email
     * @return string
     */
    public function get_note()
    {
        if ($this->note) {
            return $this->note;
        }

        $this->note = get_post_meta($this->id, 'wcgc_note', true);
        return apply_filters(WCGC()->plugin_prefix . 'get_gift_card_note', $this->note, $this->id);
    }

    /**
     * Retrieve the gift card to email
     * @return string
     */
    public function get_amount()
    {
        if ($this->amount) {
            return $this->amount;
        }

        $this->amount = get_post_meta($this->id, 'wcgc_amount', true);
        return (float) apply_filters(WCGC()->plugin_prefix . 'get_gift_card_amount', $this->amount, $this->id);
    }

    /**
     * Retrieve the gift card to email
     * @return string
     */
    public function get_balance()
    {
        if ($this->balance) {
            return $this->balance;
        }

        $this->balance = get_post_meta($this->id, 'wcgc_balance', true);
        return (float) apply_filters(WCGC()->plugin_prefix . 'get_gift_card_balance', $this->balance, $this->id);
    }

    /**
     * Retrieve the gift card to email
     * @return string
     */
    public function get_expiry_date()
    {
        if ($this->expiry_date) {
            return $this->expiry_date;
        }

        $this->expiry_date = get_post_meta($this->id, 'wcgc_expiry_date', true);
        return apply_filters(WCGC()->plugin_prefix . 'get_gift_card_expiry_date', $this->expiry_date, $this->id);
    }

    /**
     *
     */
    public function get_email_sent()
    {
        if (isset($this->is_email_sent)) {
            return $this->is_email_sent;
        }

        return $this->is_email_sent = get_post_meta($this->id, 'wcgc_email_sent', true);
    }

    /**
     *
     */
    public function set_email_sent()
    {
        update_post_meta($this->id, 'wcgc_email_sent', true);
    }

    /**
     *
     */
    public function is_expired()
    {
        $expiry_date = $this->get_expiry_date();

        // No expiry date set
        if (! $expiry_date) {
            return false;
        }

        $now = current_time('timestamp');

        return ($now >= strtotime($expiry_date));
    }
}
