<?php

require_once '../mexbt.php';

echo "Running sandbox functions.\n\n";

$ctx = new mexbt();
$ctx->public_key = "aaaa";
$ctx->private_key = "aaaa";
$ctx->user_id = "aaaa";
$ctx->sandbox = true;

//

echo "Balance: " . $ctx->balance() . "\n\n";
echo "Cancel all: " . $ctx->cancel_all_orders() . "\n\n";
echo "Buy some: " . $ctx->create_order(NULL, true, "market", 1.0) . "\n\n";
echo "Trades: " . $ctx->trades() . "\n\n";
echo "Orders: " . $ctx->orders() . "\n\n";
echo "Info: " . $ctx->info() . "\n\n";
echo "Deposit addresses: " . $ctx->deposit_addresses() . "\n\n";
echo "BTC deposit: " . $ctx->deposit_address("BTC") . "\n\n";
echo "LTC deposit: " . $ctx->deposit_address("ltc") . "\n\n";


?>
