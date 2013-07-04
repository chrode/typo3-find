<?php
/*******************************************************************************
 * Copyright notice
 *
 * Copyright 2013 Sven-S. Porst, Göttingen State and University Library
 *                <porst@sub.uni-goettingen.de>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 ******************************************************************************/


/**
 * View Helper for styling the content of index document’s result fields.
 * Requires the query result object for finding the information as well as the
 * document and the field to work on.
 *
 * Expects to find the document’s id in the field »id« which can be overridden
 * using the »idKey« parameter.
 *
 * Tries to avoid issues with creating invalid markup by assuming the highlighted
 * parts of the string are marked by Unicode Private Use Area characters
 * \ueeee and \ueeef. Then replaces these by tags for an em.highlight element.
 * The highlighting tags can be configured using the highlightTagOpen and
 * highlightTagClose arguments.
 */
class Tx_Find_ViewHelpers_HighlightFieldViewHelper extends Tx_Fluid_Core_ViewHelper_AbstractViewHelper {

	/**
	 * Registers own arguments.
	 */
	public function initializeArguments () {
		parent::initializeArguments();
		$this->registerArgument('results', 'Solarium\QueryType\Select\Result\Result', 'Query results', TRUE);
		$this->registerArgument('document', 'Solarium\QueryType\Select\Result\Document', 'Result document to work on', TRUE);
		$this->registerArgument('field', 'string', 'name of field in document to highlight', TRUE);
		$this->registerArgument('index', 'int', 'if the field as an array: index of the single element to highlight', FALSE);
		$this->registerArgument('idKey', 'string', 'name of the field in document that is its ID', FALSE, 'id');
		$this->registerArgument('highlightTagOpen', 'string', 'opening tag to insert to begin highlighting', FALSE, '<em class="highlight">');
		$this->registerArgument('highlightTagClose', 'string', 'closing tag to insert to end highlighting', FALSE, '</em>');
		$this->registerArgument('raw', 'boolean', 'whether to HTML escape the output or not', FALSE, FALSE);
	}


	
	/**
	 * @return string
	 */
	public function render () {
		if ($this->arguments['document']) {
			$fields = $this->arguments['document']->getFields();
			$field = $fields[$this->arguments['field']];
			if ($this->arguments['index'] !== NULL) {
				if (is_array($field) && count($field) > $this->arguments['index']) {
					$field = $field[$this->arguments['index']];
				}
				else {
					// TODO: error message
				}
			}

			return $this->highlightField($field);
		}
	}



	/**
	 * Returns string or array of strings with highlighted areas enclosed
	 * by \ueeee and \ueeef.
	 *
	 * @param array|string $fieldContent content of the field to highlight
	 * @return array|string
	 */
	private function highlightField ($fieldContent) {
		$result = $fieldContent;
		$highlightInfo = $this->getHighlightInfo();

		if (is_array($fieldContent)) {
			$result = array();
			foreach ($fieldContent as $singleField) {
				$result[] = $this->highlightSingleField($singleField, $highlightInfo);
			}
		}
		else {
			$result = $this->highlightSingleField($fieldContent, $highlightInfo);
		}

		return $result;
	}



	/**
	 * Returns $fieldString with highlighted areas enclosed by \ueeee and \ueeef.
	 *
	 * @param string $fieldString the string to highlight
	 * @param type $highlightInfo information provided by the index’ highlighter
	 * @return string
	 */
	private function highlightSingleField ($fieldString, $highlightInfo){
		$result = NULL;

		foreach ($highlightInfo as $highlightItem) {
			$highlightItemStripped = str_replace(array('\ueeee', '\ueeef'), array('', ''), $highlightItem);
			if (strpos($fieldString, $highlightItemStripped) !== NULL) {
				// HTML escape the text here if not explicitly configured to not do so.
				// Use f:format.raw in the template to avoid double escaping the HTML tags.
				if (!$this->arguments['raw']) {
					$highlightItem = htmlspecialchars($highlightItem);
				}

				$highlightItemMarkedUp = str_replace(
											array('\ueeee', '\ueeef'),
											array($this->arguments['highlightTagOpen'],	$this->arguments['highlightTagClose']),
											$highlightItem );
				$result = str_replace($highlightItemStripped, $highlightItemMarkedUp, $fieldString);
				break;
			}
		}

		// If no highlighted string is present, use the original one.
		if ($result === NULL) {
			if ($this->arguments['raw']) {
				$result = $fieldString;
			}
			else {
				$result = htmlspecialchars($fieldString);
			}
		}

		return $result;
	}



	/**
	 * Returns highlight information for the document and field configured in
	 * our arguments.
	 *
	 * @return array
	 */
	private function getHighlightInfo () {
		$documentID = $this->arguments['document'][$this->arguments['idKey']];
		$highlighting =  $this->arguments['results']->getHighlighting();
		if ($highlighting) {
			$highlightInfo = $highlighting->getResult($documentID)->getField($this->arguments['field']);
		}

		return $highlightInfo;
	}

}

?>