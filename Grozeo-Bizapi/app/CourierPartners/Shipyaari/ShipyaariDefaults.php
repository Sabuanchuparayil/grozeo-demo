<?php

namespace App\CourierPartners\Shipyaari;

class ShipyaariDefaults
{
	public $partners = [
		["id"	=> "9",		"name"	=> "Delhivery"],
		["id"	=> "6406",	"name"	=> "Ekart"],
		["id"	=> "5161",	"name"	=> "XpressBees"],
		["id"	=> "2354",	"name"	=> "Bluedart"],
		["id"	=> "2",		"name"	=> "DTDC"],
		["id"	=> "19550",	"name"	=> "Amazon (SWA)"]
	];

	public $weightClass = [
		["weight"	=> '0.5',	"name"	=> "Standard"],
		["weight"	=> '0.5',	"name"	=> "Economy 0.5kg"],
		["weight"	=> '1',		"name"	=> "Economy 1kg"],
		["weight"	=> '2',		"name"	=> "Economy 2Kgs"],
		["weight"	=> '3',		"name"	=> "Economy 3Kgs"],
		["weight"	=> '5',		"name"	=> "Economy 5kgs"],
		["weight"	=> '7',		"name"	=> "Economy 7Kgs"],
		["weight"	=> '10',	"name"	=> "Economy"],
		["weight"	=> '20',	"name"	=> "Economy 20kgs"],
		["weight"	=> '30',	"name"	=> "Economy 30kgs"],
		["weight"	=> '50',	"name"	=> "Economy 50kgs"],
	];

	public $trackingStatusCodes = [
		"1"		=> "Not Picked",
		"2"		=> "In Transit",
		"3"		=> "Out For Delivery",
		"4"		=> "Delivered",
		"5"		=> "RTO In Transit",
		"6"		=> "RTO Delivered",
		"7"		=> "Exception",
		"8"		=> "Lost/Damage",
		"9"		=> "Reverse Delivered",
		"10"	=> "RTO Exception",
		"11"	=> "RTO Created",
		"12"	=> "RTO out for delivery",
		"14"	=> "Reached Destination",
		"15"	=> "Reverse In Transit",
		"16"	=> "Reverse Exception",
		"17"	=> "Reverse Out for Deli",
		"18"	=> "Cancelled"
	];
}