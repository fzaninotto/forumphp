<?php

require_once 'level2/Book.php';
require_once 'level2/Author.php';
require_once 'level2/Query.php';

$dsn = 'mysql:host=localhost;dbname=bookstore';
$user = 'fzaninotto';
$pass = 'S3Cr3t';

try {
	
	// opening the connection
	$con = new PDO($dsn, $user, $pass);
	$con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$con->beginTransaction();
	
	// inserting an author
	$author = new Author();
	$author->first_name = 'Leo';
	$author->last_name = 'Tolstoi';
	$author->save($con);

	// inserting a couple books
	$book1 = new Book();
	$book1->title = 'War and Peace';
	$book1->author_id = $author->id;
	$book1->save($con);
	$book2 = new Book();
	$book2->title = 'Anna Karenina';
	$book2->author_id = $author->id;
	$book2->save($con); 
  
	// selecting a book by Tolstoi named 'War%'
	$query = new Query('Book');
	$query->join('author', '`book`.`author_id` = `author`.`id`');
	$query->where('`author`.`last_name` = ?', 'Tolstoi', PDO::PARAM_STR);
	$query->where('`book`.`title` LIKE ? ', 'War%', PDO::PARAM_STR);
	if ($book = $query->findOne($con)) {
		printf('Book of id %d, named %s, written by %s',
			$book->id, $book->title, $book->getAuthor($con)->getName()
		);
	}
  
	// selecting all books
	$query = new Query('Book');
	$books = $query->find($con);
	foreach ($books as $book) {
		printf("Book of id %d, named %s, written by %s\n",
			$book->id, $book->title, $book->getAuthor($con)->getName()
		);
	}
	if (!$books) {
		echo "no result\n";
	}
	
	// commit the transaction
	$con->commit();
	
	// close the connection
	$con = null;

} catch (PDOException $e) {
  
	$con->rollBack();
	printf("Failed: %s\n", $e->getMessage());
}
