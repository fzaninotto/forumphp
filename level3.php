<?php

require_once 'vendor/propel/runtime/lib/Propel.php';
set_include_path(dirname(__FILE__) . '/level3/build/classes' . PATH_SEPARATOR . get_include_path());
Propel::init(dirname(__FILE__) . '/level3/build/conf/bookstore-conf.php');

try {
	
	// opening the connection
	$con = Propel::getConnection();
	$con->beginTransaction();
	
	// inserting an author
	$author = new Author();
	$author->setFirstName('Leo');
	$author->setLastName('Tolstoi');
	$author->save($con);

	// inserting a couple books
	$book1 = new Book();
	$book1->setTitle('War and Peace');
	$book1->setAuthor($author);
	$book1->save($con);
	$book2 = new Book();
	$book2->setTitle('Anna Karenina');
	$book2->setAuthor($author);
	$book2->save($con); 
  
	// selecting a book by Tolstoi named 'War%'
	$book = BookQuery::create()
		->useAuthorQuery()
			->filterByLastName('Tosltoi')
		->endUse()
		->filterByTitle('War%')
		->with('Author')
		->findOne($con);
	if ($book) {
		printf('Book of id %d, named %s, written by %s',
			$book->getId(), $book->getTitle(), $book->getAuthor($con)->getName()
		);
	}
  
	// selecting all books
	$books = BookQuery::create()->find($con);
	foreach ($books as $book) {
		printf("Book of id %d, named %s, written by %s\n",
			$book->getId(), $book->getTitle(), $book->getAuthor($con)->getName()
		);
	}
	if (!$books->count()) {
		echo "no result\n";
	}
	
	// commit the transaction
	$con->commit();

} catch (PropelException $e) {
  
	$con->rollBack();
	printf("Failed: %s\n", $e->getMessage());
}
