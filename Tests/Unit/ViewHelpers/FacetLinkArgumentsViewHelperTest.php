<?php

/* * *************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Ingo Pfennigstorf <pfennigstorf@sub-goettingen.de>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 * ************************************************************* */

/**
 * Test for FacetLinkArguments ViewHelper
 */
class Tx_FindTests_Unit_ViewHelpers_FacetLinkArgumentsViewHelperTest extends Tx_Phpunit_TestCase {

	/**
	 * @var Tx_Find_ViewHelpers_FacetLinkArgumentsViewHelper
	 */
	public $fixture;

	public function setUp() {
		$this->fixture = new Tx_Find_ViewHelpers_FacetLinkArgumentsViewHelper();
	}

	/**
	 * @test
	 */
	public function filterIsCorrectlyRemovedOnTextQueries() {
		$this->fixture->setArguments(array(
			'facetName' => 'title',
			'itemName' => 'hrdr',
			'activeFacets' => array('title:"hrdr"'),
			'mode' => 'remove'
		));

		$result = $this->fixture->render();
		$this->assertEquals("tx_find_find[facet][0]", $result[0]);
	}

	/**
	 * @test
	 */
	public function filterIsCorrectlyAddedOnTextQueries() {
		$this->fixture->setArguments(array(
			'facetName' => 'title',
			'itemName' => 'hrdr',
			'mode' => 'add'
		));

		$result = $this->fixture->render();

		$this->assertEquals('title:"hrdr"', $result['facet'][0]);
	}

}