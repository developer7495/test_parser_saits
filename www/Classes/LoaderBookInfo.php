<?php
/**
 * @autor Anatoliy Lazarev <software7528developer@yandex.ru>
 */

namespace TPLA;

use \KubAT\PhpSimple\HtmlDomParser as SimpleHtmlDom;
use \Exception;

class LoaderBookInfo
{
	private   $configParams    = [];
	protected $connect_timeout = 10000; //ms
	
	function __construct(){
		$arrConfigStr = file('secret.ini', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		$config = [];
		
		foreach($arrConfigStr as $str){
			$str   = explode('=', $str);
			$key   = trim($str[0]);
			$value = preg_replace('/(\'|\")/', '', trim($str[1]));
			
			$config[$key] = $value;
		}
		
		$this->configParams = $config;
	}
	
	public function getFromAmazonByApi(\TPLA\SearchItemsRequest $searchItemsRequest, string $isbn){
		/**
		 * Copyright 2019 Amazon.com, Inc. or its affiliates. All Rights Reserved.
		 *
		 * Licensed under the Apache License, Version 2.0 (the "License").
		 * You may not use this file except in compliance with the License.
		 * A copy of the License is located at
		 *
		 *     http://www.apache.org/licenses/LICENSE-2.0
		 *
		 * or in the "license" file accompanying this file. This file is distributed
		 * on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either
		 * express or implied. See the License for the specific language governing
		 * permissions and limitations under the License.
		 */
		$searchItemsRequest->PartnerType = "Associates";
		// Put your Partner tag (Store/Tracking id) in place of Partner tag
		$searchItemsRequest->PartnerTag  = $config['amazon_partner_tag'] ?? '';
		$searchItemsRequest->ItemIds     = [$isbn];
		//$searchItemsRequest->ItemIdType  = "ISBN";
		$searchItemsRequest->ItemIdType  = "ISIN";
		$searchItemsRequest->Resources   = ["Images.Primary.Small", "ItemInfo.Title", "Offers.Listings.Price"];
		$host                           = "webservices.amazon.com";
		$path                           = "/paapi5/searchitems";
		$payload                        = json_encode($searchItemsRequest);
		
		//Put your Access Key in place of <ACCESS_KEY> and Secret Key in place of <SECRET_KEY> in double quotes
		$awsv4 = new AwsV4 ($config['amazon_access_key'] ?? '', $config['amazon_secret_key'] ?? ''); //todo Bad practice to call a class in a class
		$awsv4->setRegionName("us-east-1");
		$awsv4->setServiceName("ProductAdvertisingAPI");
		$awsv4->setPath($path);
		$awsv4->setPayload($payload);
		$awsv4->setRequestMethod("POST");
		$awsv4->addHeader('content-encoding', 'amz-1.0');
		$awsv4->addHeader('content-type', 'application/json; charset=utf-8');
		$awsv4->addHeader('host', $host);
		$awsv4->addHeader(
			'x-amz-target',
			'com.amazon.paapi5.v1.ProductAdvertisingAPIv1.SearchItems'
		);
		$headers      = $awsv4->getHeaders();
		$headerString = "";
		foreach($headers as $key => $value){
			$headerString .= $key . ': ' . $value . "\r\n";
		}
		$params = [
			'http' => [
				'header'  => $headerString,
				'method'  => 'POST',
				'content' => $payload,
			],
		];
		$stream = stream_context_create($params);
		
		$fp = @fopen('https://' . $host . $path, 'rb', false, $stream);
		
		if(!$fp){
			return "Exception Occured";
		}
		$response = @stream_get_contents($fp);
		if($response === false){
			return "Exception Occured";
		}
		
		return $response;
	}
	
	public function grabFromAmazon(string $isbn){
		$url = "https://www.amazon.com/s?k={$isbn}&i=stripbooks-intl-ship&ref=nb_sb_noss";
		try{
			$tmp = file_get_contents($url);
			return file_get_contents($url);
			
		}catch(Exception $e){
			return $this->makeErrorHtml('Error get amazon page!');
		}
	}
	
	public function grabFromBookdepository(string $isbn){
		$url = "https://www.bookdepository.com/suggestions?searchTerm={$isbn}&search=Find+book";
	
		try{
			return file_get_contents($url);
			
		}catch(Exception $e){
			return $this->makeErrorHtml('Error get Bookdepository page!');
		}
	}
	
	public function makeErrorHtml(string $strErr){
		return '<div id="err_get_page">' .$strErr. '</div>';
	}
}