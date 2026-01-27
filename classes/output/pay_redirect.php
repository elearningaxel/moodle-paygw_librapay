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
 * Renderable for LibraPay payment redirect page.
 *
 * @package    paygw_librapay
 * @copyright  2026 Axel eLearning
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace paygw_librapay\output;

use renderable;
use renderer_base;
use templatable;
use stdClass;

/**
 * Class containing data for LibraPay payment redirect page.
 *
 * @copyright  2026 Axel eLearning
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class pay_redirect implements renderable, templatable {
    /** @var string LibraPay gateway URL */
    protected $librapayurl;

    /** @var string Payment amount */
    protected $amount;

    /** @var string Currency code */
    protected $currency;

    /** @var string Order ID */
    protected $orderid;

    /** @var string Payment description */
    protected $description;

    /** @var string Terminal ID */
    protected $terminal;

    /** @var string Request timestamp */
    protected $timestamp;

    /** @var string Request nonce */
    protected $nonce;

    /** @var string Callback URL */
    protected $backref;

    /** @var string Custom data */
    protected $datacustom;

    /** @var string Request signature */
    protected $psign;

    /**
     * Constructor.
     *
     * @param string $librapayurl LibraPay gateway URL
     * @param string $amount Payment amount
     * @param string $currency Currency code
     * @param string $orderid Order ID
     * @param string $description Payment description
     * @param string $terminal Terminal ID
     * @param string $timestamp Request timestamp
     * @param string $nonce Request nonce
     * @param string $backref Callback URL
     * @param string $datacustom Custom data
     * @param string $psign Request signature
     */
    public function __construct(
        string $librapayurl,
        string $amount,
        string $currency,
        string $orderid,
        string $description,
        string $terminal,
        string $timestamp,
        string $nonce,
        string $backref,
        string $datacustom,
        string $psign
    ) {
        $this->librapayurl = $librapayurl;
        $this->amount = $amount;
        $this->currency = $currency;
        $this->orderid = $orderid;
        $this->description = $description;
        $this->terminal = $terminal;
        $this->timestamp = $timestamp;
        $this->nonce = $nonce;
        $this->backref = $backref;
        $this->datacustom = $datacustom;
        $this->psign = $psign;
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param renderer_base $output
     * @return stdClass
     */
    public function export_for_template(renderer_base $output): stdClass {
        $data = new stdClass();
        $data->librapayurl = $this->librapayurl;
        $data->amount = $this->amount;
        $data->currency = $this->currency;
        $data->orderid = $this->orderid;
        $data->description = $this->description;
        $data->terminal = $this->terminal;
        $data->timestamp = $this->timestamp;
        $data->nonce = $this->nonce;
        $data->backref = $this->backref;
        $data->datacustom = $this->datacustom;
        $data->psign = $this->psign;
        $data->redirectingstr = get_string('redirecting', 'paygw_librapay');
        $data->redirectingtolibrapaystr = get_string('redirectingtolibrapay', 'paygw_librapay');
        $data->noscriptstr = get_string('noscript', 'paygw_librapay');
        $data->continuetopaymentstr = get_string('continuetopayment', 'paygw_librapay');
        return $data;
    }
}
