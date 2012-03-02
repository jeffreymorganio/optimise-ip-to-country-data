optimiseIP2Country.php
======================

About
-----

The `optimiseIP2Country.php` PHP script takes an IP to country CSV data file and optimises it by:

1. removing the three-letter country code and country name;
2. collapsing sequential entries from the same country; and
3. not double-quoting values in the CSV output.

The expected format of the IP to country CSV file is as follows:

    "16777216","17367039","AU","AUS","AUSTRALIA"

(If the input file is not in this format, it is simple enough to change the regular expression that parses the CSV data in the `parseLineOfData` function.)

The output produced by the script is in the following format:

    16777216,17367039,AU

For simplicity, this script terminates if it encounters lines in the input file that are blank or that cannot be parsed (which should not occur in correctly-formatted IP to country CSV files).

Collapsing Sequential Entries
-----------------------------

The following example illustrates the collapsing of sequential entries. The start IP address of the second entry is one more than the end IP address of the first entry:

    Start IP  End IP    Country Code
    982122496,982155263,JP
    982155264,982171647,JP

Therefore, we can merge these two entries into the following single entry:

    982122496,982171647,JP

Use
---

Call `optimiseIP2Country.php` on the command line :

    optimiseIP2Country.php INPUT_FILENAME > OUTPUT_FILENAME

Example:

    optimiseIP2Country.php ip-to-country.csv > optimised-ip-to-country.csv

More Information
----------------

For a complete description of this optimisation technique, please read [Optimising IP Geolocation Data](http://usabilityetc.com/articles/optimising-geolocation-data/).
