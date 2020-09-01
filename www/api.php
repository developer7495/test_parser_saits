<?php

$nameShopApi    = $_GET['api'] ?? null;
$isbn           = $_GET['isbn'] ?? null;
$allowedApi     = ['amazon', 'bookdepository'];
$loaderBookInfo = new \TPLA\LoaderBookInfo();

// Check params
if(!$nameShopApi || !$isbn || !in_array($nameShopApi, $allowedApi)){
	echo $loaderBookInfo->makeErrorHtml('Invalid params!');
	return;
}

echo $nameShopApi === 'amazon'
	? $loaderBookInfo->grabFromAmazon($isbn)
	: $loaderBookInfo->grabFromBookdepository($isbn);
