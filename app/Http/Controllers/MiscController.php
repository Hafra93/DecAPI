<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\Helpers\Helper;
use Carbon\Carbon;
use DateTimeZone;

class MiscController extends Controller
{
    /**
     * Converts currency based on parameters passed.
     *
     * @param  Request $request
     * @return Response
     */
    public function currency(Request $request)
    {
        $value = $request->input('value', null);
        $from = $request->input('from', null);
        $to = $request->input('to', null);
        $round = intval($request->input('round', 2));
        $currencies = ["AUD", "BGN", "BRL", "CAD", "CHF", "CNY", "CZK", "DKK", "EUR", "GBP", "HKD", "HRK", "HUF", "IDR", "ILS", "INR", "JPY", "KRW", "MXN", "MYR", "NOK", "NZD", "PHP", "PLN", "RON", "RUB", "SEK", "SGD", "THB", "TRY", "USD", "ZAR"];

        $listUrl = route('misc.currency', 'currency') . "?list";

        if ($request->exists('list')) {
            return Helper::text('Available currencies: ' . implode(', ', $currencies));
        }

        if (empty($value)) {
            return Helper::text('The "value" parameter has to be specified');
        }

        if (empty($from)) {
            return Helper::text('The "from" parameter has to be specified');
        }

        if (empty($to)) {
            return Helper::text('The "to" parameter has to be specified');
        }

        $value = floatval(str_replace(',', null, $value));
        $from = strtoupper(trim($from));
        $to = strtoupper(trim($to));

        if ($value === 0) {
            $value = 1.00;
        }

        if (!in_array($from, $currencies)) {
            return Helper::text('Invalid "from" currency specified - Available currencies can be found here: ' . $listUrl);
        }

        if (!in_array($to, $currencies)) {
            return Helper::text('Invalid "to" currency specified - Available currencies can be found here: ' . $listUrl);
        }

        $convert = Helper::get('http://api.fixer.io/latest?base=' . $from . '&symbols=' . $to);

        if (empty($convert['rates'][$to])) {
            return Helper::text('An error has occurred retrieving exchange rates');
        }

        $calculate = round($value * $convert['rates'][$to], $round);
        return Helper::text(sprintf('%s %s = %s %s', $value, $from, $calculate, $to));
    }

    /**
     * Display the current time in the specified timezone.
     *
     * @param  Request $request
     * @return Response
     */
    public function time(Request $request)
    {
        $format = $request->input('format', 'h:i:s A T');
        $tz = $request->input('timezone', null);
        $timezones = DateTimeZone::listIdentifiers();
        $tzlist = implode(PHP_EOL, $timezones);
        if (empty($tz)) {
            return Helper::text("-- Available timezones:" . PHP_EOL . $tzlist);
        }

        if (!in_array($tz, $timezones)) {
            return Helper::text(sprintf('-- Invalid timezone ("%s") - Available timezones:%s%s', $tz, PHP_EOL, $tzlist));
        }

        $time = Carbon::now()
                ->tz($tz)
                ->format($format);
        return Helper::text($time);
    }
}
