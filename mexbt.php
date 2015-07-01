<?php

/*
Copyright (c) 2015 meXBT

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
*/

//error_reporting(E_ALL | E_STRICT);

class mexbt
{
	public $public_key;
	public $private_key;
	public $user_id;
	public $currency_pair = "btcmxn";
	public $sandbox = false;

	/**
	 * Format the float to a string at required precision.
	 *
	 * @param float $num
	 * @return string Formatted number.
	 */
	private function format_float($num) {
		if (!is_null($this->sandbox) && $this->sandbox)
			return sprintf("%.6f", $num);
		else
			return sprintf("%.8f", $num);
	}

	/**
	 * Send this JSON and return the response.
	 *
	 * @param string $name
	 * @param array $arr
	 * @return string The JSON.
	 */
	private function call($name, $arr, $private_call = true) {
		$url = 'https://public-api.mexbt.com';

		if ($private_call) {
			if (is_null($this->public_key) ||
				is_null($this->private_key) ||
				is_null($this->user_id) ||
				is_null($this->currency_pair))
				throw new Exception("Attempted private call without configuring");

			$nonce = (int) (microtime(true) * 1000.0);

			$arr["apiKey"] = $this->public_key;
			$arr["apiNonce"] = $nonce;
			$arr["apiSig"] = strtoupper(hash_hmac("sha256",
							$nonce . $this->user_id . $this->public_key,
							$this->private_key));

			$url = 'https://private-api.mexbt.com';
			if (!is_null($this->sandbox) && $this->sandbox)
				$url = 'https://private-api-sandbox.mexbt.com';
		}

		$payload = json_encode($arr);
//		echo "Payload $payload\n";

		$url .= "/v1/" . $name;
//		echo "Url $url\n";

		$options = array(
			'http' => array(
				'header' => "Content-type: JSON\r\n",
				'method' => 'POST',
				'content' => $payload,
			));
		$ctx = stream_context_create($options);
		$result = file_get_contents($url, false, $ctx);

		if (empty($result))
			throw new Exception("Empty response from API");

		$arr = json_decode($result, true);
		$accepted = $arr["isAccepted"];

		if (!$accepted)
			throw new Exception($arr["rejectReason"]);

		return $result;
	}

	//
	//
	//
	// Public API
	//
	//
	//

	/**
	 * Returns the current ticker data for this pair.
	 *
	 * @param string $currency_pair
	 * @return string The JSON.
	 */
	public function ticker($currency_pair = NULL) {

		if (is_null($currency_pair))
			$currency_pair = $this->currency_pair;

		$arr = array("productPair" => $currency_pair);

		return $this->call("ticker", $arr, false);
	}

	/**
	 * Returns the current orderbook for a given currency pair.
	 *
	 * @param string $currency_pair
	 * @return string The JSON.
	 */
	public function order_book($currency_pair = NULL) {

		if (is_null($currency_pair))
			$currency_pair = $this->currency_pair;

		$arr = array("productPair" => $currency_pair);

		return $this->call("order-book", $arr, false);
	}

	/**
	 * Returns the last N trades.
	 *
	 * @param string $currency_pair
	 * @param int $start_index
	 * @param int $count
	 * @return string The JSON.
	 */
	public function public_trades($currency_pair = NULL, $start_index = -1,
					$count = 10) {
		if (is_null($currency_pair))
			$currency_pair = $this->currency_pair;

		$arr = array("ins" => $currency_pair, "startIndex" => $start_index,
				"count" => $count);

		return $this->call("trades", $arr, false);
	}

	/**
	 * Returns trades between these dates, in unix seconds.
	 *
	 * @param string $currency_pair
	 * @param int $start_date
	 * @param int $end_date
	 * @return string The JSON.
	 */
	public function trades_by_date($currency_pair = NULL, $start_date, $end_date) {

		if (is_null($currency_pair))
			$currency_pair = $this->currency_pair;

		$arr = array("ins" => $currency_pair, "startDate" => $start_date,
				"endDate" => $end_date);

		return $this->call("trades-by-date", $arr, false);
	}

	//
	//
	//
	// Private API
	//
	//
	//

	/**
	 * Create a market or limit order.
	 *
	 * @param string $currency_pair
	 * @param boolean $buying
	 * @param string $ordertype
	 * @param float $qty
	 * @param float $price
	 * @return string The JSON.
	 */
	public function create_order($currency_pair = NULL, $buying, $ordertype = "market",
					$qty, $price = NULL) {

		if (is_null($currency_pair))
			$currency_pair = $this->currency_pair;

		$arr = array("ins" => $currency_pair,
				"side" => $buying ? "buy" : "sell",
				"orderType" => $ordertype === "market" ? 1 : 0,
				"qty" => $this->format_float($qty));

		if ($ordertype !== "market")
			$arr["px"] = $price;

		return $this->call("orders/create", $arr);
	}

	/**
	 * Cancel all orders for this currency pair.
	 *
	 * @param string $currency_pair
	 * @return string The JSON.
	 */
	public function cancel_all_orders($currency_pair = NULL) {

		if (is_null($currency_pair))
			$currency_pair = $this->currency_pair;

		$arr = array("ins" => $currency_pair);

		return $this->call("orders/cancel-all", $arr);
	}

	/**
	 * Returns all your balances.
	 *
	 * @return string The JSON.
	 */
	public function balance() {

		$arr = array();

		return $this->call("balance", $arr);
	}

	/**
	 * Returns your trades in these bounds.
	 *
	 * @param string $currency_pair
	 * @param int $start_index
	 * @param int $count
	 * @return string The JSON.
	 */
	public function trades($currency_pair = NULL, $start_index = -1,
					$count = 10) {
		if (is_null($currency_pair))
			$currency_pair = $this->currency_pair;

		$arr = array("ins" => $currency_pair, "startIndex" => $start_index,
				"count" => $count);

		return $this->call("trades", $arr);
	}

	/**
	 * Returns your orders.
	 *
	 * @return string The JSON.
	 */
	public function orders() {

		$arr = array();

		return $this->call("orders", $arr);
	}

	/**
	 * Withdraw this amount.
	 *
	 * @param float $amount
	 * @param string $address
	 * @param string $currency
	 * @return string The JSON.
	 */
	public function withdraw($amount, $address, $currency = "btc") {

		$printed = $this->format_float($amount);

		$arr = array("ins" => $currency, "amount" => $printed,
				"sendToAddress" => $address);

		return $this->call("withdraw", $arr);
	}

	/**
	 * Returns your account info.
	 *
	 * @return string The JSON.
	 */
	public function info() {

		$arr = array();

		return $this->call("me", $arr);
	}

	/**
	 * Returns your deposit addresses.
	 *
	 * @return string The JSON.
	 */
	public function deposit_addresses() {

		$arr = array();

		return $this->call("deposit-addresses", $arr);
	}

	/**
	 * Returns your deposit address for this currency (BTC, LTC...).
	 *
	 * @param string $currency
	 * @return string The address.
	 */
	public function deposit_address($currency) {

		$currency = strtoupper($currency);
		$arr = array();

		$res = $this->call("deposit-addresses", $arr);
		$res = json_decode($res, true);
		$res = $res["addresses"];

		for ($i = 0; $i < count($res); $i++) {
			$tmp = $res[$i];
			if ($tmp["name"] === $currency)
				return $tmp["depositAddress"];
		}

		throw new Exception("No $currency deposit address");
	}
}

?>
