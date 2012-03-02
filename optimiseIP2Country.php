#!/usr/bin/php
<?php

/*
 * optimiseIP2Country.php
 *
 * by Jeffrey Morgan
 * http://usabilityetc.com/
 *
 * Optimise IP to country CSV files by:
 *   1. removing the three-letter country code and country name;
 *   2. collapsing sequential entries from the same country; and
 *   3. not double-quoting values in the CSV output.
 *
 * Expected input file format:
 *
 *   "16777216","17367039","AU","AUS","AUSTRALIA"
 *
 * Output file format:
 *
 *   16777216,17367039,AU
 *
 * For simplicity, this script terminates if it encounters lines
 * in the input file that are blank or that cannot be parsed (which
 * should not occur in correctly-formatted IP to country CSV files).
 *
 * Use:
 *
 *   optimiseIP2Country.php INPUT_FILENAME > OUTPUT_FILENAME
 *
 * Example:
 *
 *   optimiseIP2Country.php ip-to-country.csv > optimised-ip-to-country.csv
 */

optimise($argv, $argc);

function optimise($argv, $argc)
{
	// Open the input file
	$inputFileHandle = openFile($argc, $argv);

	// Read and parse the first line of data
	$lineOfData = readLineOfData($inputFileHandle);
	list($startIPAddressA, $endIPAddressA, $countryCodeA) = parseLineOfData($lineOfData);

	// Read and parse each subsequent line of data
	while ($lineOfData = readLineOfData($inputFileHandle)) {
		list($startIPAddressB, $endIPAddressB, $countryCodeB) = parseLineOfData($lineOfData);

		// If the country code of the first line (A) is the same as the country code of the
		// second line (B) and the start IP address of the second line follows on from
		// (i.e. is one more than) the end IP address of the the first line, then collapse
		// the data on the second line into the first line.
		if (($countryCodeA == $countryCodeB) && ($startIPAddressB == $endIPAddressA + 1)) {
			$endIPAddressA = $endIPAddressB;
			continue;
		}
		
		// Write the current line of data in CSV format
		writeLineOfData($startIPAddressA, $endIPAddressA, $countryCodeA);
		
		// Set the data in the first line (A) to the data in the second line (B)
		// to be able to compare the data in the new second line next time around
		$startIPAddressA = $startIPAddressB;
		$endIPAddressA = $endIPAddressB;
		$countryCodeA = $countryCodeB;
	}
	
	// Write the final line of data in CSV format
	writeLineOfData($startIPAddressA, $endIPAddressA, $countryCodeA);

	// Close the input file
	fclose($inputFileHandle);
}

/*
 * Open the input file of IP to country data using the
 * filename supplied as a command-line argument.
 */
function openFile($argc, $argv)
{
	if ($argc != 2) {
		dieWithErrorMessage("Please supply input filename");
	}
	
	// Use the supplied input filename
	$inputFilename = $argv[1];

	// Open the input file
	if (file_exists($inputFilename)) {
		$inputFileHandle = fopen($inputFilename, 'r');
	} else {
		dieWithErrorMessage("Cannot read from file '$inputFilename'");
	}
	
	return $inputFileHandle;
}

/*
 * Read and return the next line of IP to country data with
 * null indicating the end of the file.
 */
function readLineOfData($inputFileHandle)
{	
	// Return null at the end of file
	if (feof($inputFileHandle)) {
		return null;
	}
	
	// Read the next line and remove any leading or trailing whitespace
	$lineOfData = trim(fgets($inputFileHandle));

	return $lineOfData;
}

/*
 * Output the start IP address, the end IP address,
 * and the two-letter country code as a line of CSV.
 */
function writeLineOfData($startIPAddress, $endIPAddress, $countryCode)
{
	print implode(',', array($startIPAddress, $endIPAddress, $countryCode)) . "\r\n";
}

/*
 * Return the start IP address, end IP address, and the
 * two-letter country code parsed from a line of CSV.
 */
function parseLineOfData($lineOfData)
{
	// The regexp for parsing a line of IP to country data that captures
	// the start IP address, the end IP address, and the two-letter country code
	// while igonoring the rest of the line
	$IP2COUNTRY_REGEXP = '/"(\d{1,10})","(\d{1,10})","([A-Z]{2})"/';
	
	// Parse a line of IP to country data
	$matches = preg_match($IP2COUNTRY_REGEXP, $lineOfData, $regexpMatches);
	
	// Halt if parsing fails
	if (0 == $matches) {
		dieWithErrorMessage("Cannot parse line:\n\t$lineOfData");
	}
	
	// Remove the first regexp match (which is the whole line of data)
	array_shift($regexpMatches);
	
	// Return the array of regexp matches:
	//   [0] start IP address
	//   [1] end IP address
	//   [2] two-letter country code
	return $regexpMatches;
}

function dieWithErrorMessage($errorMessage)
{
	die("optimiseIP2Country: $errorMessage\n");
}

?>
