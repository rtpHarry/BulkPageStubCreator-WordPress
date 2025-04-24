<?php

/**
* Plugin Name: Bulk Page Stub Creator
* Plugin URI: http://articles.runtings.co.uk/p/bulk-page-stub-creator-wordpress.html
* Description: Bulk create page stubs by simply providing a plain text list of titles and slugs.
* Version: 1.1
* Author: rtpHarry
* Author URI: http://articles.runtings.co.uk/
* License: GPL3
*/

/*
    BulkPageStubCreator-WordPress v1.1
    Copyright (C) 2014 Matthew Harris aka rtpHarry

    Bulk create page stubs by simply providing a plain text list of titles and slugs.

    http://articles.runtings.co.uk/p/bulk-page-stub-creator-wordpress.html

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

namespace RunthingsBulkPageStubCreator;

if (!defined('WPINC')) {
    die;
}

include ('includes/process-data.php');		// code to bulk create pages & return results
include ('includes/show-admin-page.php'); 	// admin page display logic