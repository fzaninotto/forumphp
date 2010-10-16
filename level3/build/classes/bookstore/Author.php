<?php



/**
 * Skeleton subclass for representing a row from the 'author' table.
 *
 * Author Table
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 * @package    propel.generator.bookstore
 */
class Author extends BaseAuthor {

	public function getName()
	{
		if ($this->first_name && $this->last_name) {
			return $this->first_name . ' ' . $this->last_name;
		} elseif ($this->last_name) {
			return $this->last_name;
		} elseif ($this->first_name) {
			return $this->first_name;
		} else {
			return 'Mr. Nobody';
		}
	}

} // Author
