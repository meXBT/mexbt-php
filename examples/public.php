<?php

require_once '../mexbt.php';

echo "Running all public functions.\n\n";

$ctx = new mexbt();

$now = time();


echo "Ticker data: " . $ctx->ticker() . "\n\n";
echo "Orderbook data: " . $ctx->order_book() . "\n\n";
echo "Latest ten trades: " . $ctx->public_trades() . "\n\n";
echo "All trades from last two minutes: " . $ctx->trades_by_date(NULL, $now - 120, $now) . "\n\n";


?>
