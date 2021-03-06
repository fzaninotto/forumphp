<?php

require_once 'vendor/propel/runtime/lib/Propel.php';
set_include_path(dirname(__FILE__) . '/level4/build/classes' . PATH_SEPARATOR . get_include_path());
Propel::init(dirname(__FILE__) . '/level4/build/conf/bookstore-conf.php');

// namespaces
use Bookstore\Book;
use Bookstore\BookQuery;
use Bookstore\Author;
use Bookstore\AuthorQuery;

// implicit connection

// Cascade save
$book1 = new Book();
$book1->setTitle('Harry Potter and the Philosopher\'s Stone');
$book2 = new Book();
$book2->setTitle('Harry Potter and the Chamber of Secrets');
$author = new Author();
$author->setFirstName('J.K.');
$author->setLastName('Rowling');
$author->addBook($book1);
$author->addBook($book2);
$author->save();

// Collections
$books = $author->getBooks();
echo $books->isEmpty(); // false
echo $books->count(); // 2
echo $books->getFirst()->getTitle(); // Harry Potter and the Philosopher's Stone
foreach ($books as $book) { 
  printf('<li class="%s">%s</li>',
    $books->isOdd() ? 'odd' : 'even',
    $book->getTitle()
  );
}
//<li class="even">Harry Potter and the Philosopher's Stone</li>
//<li class="odd">Harry Potter and the Chamber of Secrets</li>
unset($books[1]);
unset($books[0]);
$books []= $book1;

// Query termination methods
echo BookQuery::create()->count(); // 2
BookQuery::create()
  ->filterByTitle('Harry Potter and the%')
  ->update(array('Title' => 'Voldemort got you Pwned'));
AuthorQuery::create()
  ->filterByLastName('Rowling')
  ->delete();
echo BookQuery::create()->count(); // 0

// Identity Map
$author = new Author();
$author->setFirstName('Jane');
$author->setLastName('Austen');
$author->save();
$pk = $author->getId();
$book = new Book();
$book->setTitle('Sense and Sensibility');
$book->setAuthorId($pk);
$book->save();
$author = $book->getAuthor(); // no query
$author = AuthorQuery::create()->findPk($pk); // no query

// On-Demand Hydration
for ($i=0; $i < 1000; $i++) { 
  $book = new Book();
  $book->setTitle('book #' . $i);
  $book->save();
}
$books = BookQuery::create()
  ->setFormatter(ModelCriteria::FORMAT_ON_DEMAND)
  ->find(); // no hydration yet
foreach ($books as $book) {
  echo $book->getTitle(), "\n"; // on demand hydration
}
BookQuery::create()->forceDeleteAll();

// Validators
$book1 = new Book();
$book1->setTitle('This title is unique');
echo $book1->validate(); // true
$book1->save();
$book2 = new Book();
$book2->setTitle('This title is unique');
if ($book2->validate()) { // false
  $book2->save();
} else {
  foreach($book2->getValidationFailures() as $failure) {
    echo $failure->getMessage() , "\n"; // Book title already in database.
  }
}

// Behaviors
// soft delete a book
$book1->delete();
echo $book1->isDeleted(); // false
echo $book1->getDeletedAt(); // 2010-10-29 18:14:23
echo BookQuery::create()->count(); // 0
// recover a deleted book
BookQuery::disableSoftDelete();
echo BookQuery::create()->count(); // 1
BookQuery::disableSoftDelete();
$book = BookQuery::create()->findOne();
$book->unDelete();
BookQuery::enableSoftDelete();
echo BookQuery::create()->count(); // 1
// soft delete works on queries
BookQuery::create()->deleteAll();

// Table Inheritance
$art = new Article();
$art->setTitle('Avatar Makes Best Opening Weekend in the History');
$art->setIsPublished(true);
$art->setBody("With $232.2 million worldwide total,
Avatar had one of the best-opening weekends in the history of cinema.");
$art->save();
// create a new Video
$vid = new Video();
$vid->setTitle('Avatar Trailer');
$art->setIsPublished(true);
$vid->setUrl('http://www.avatarmovie.com/index.html');
$vid->save();

$contents = ContentQuery::create()->find();
foreach ($contents as $content) {
  echo $content->getTitle(), "\n";
}
// Avatar Makes Best Opening Weekend in the History
// Avatar Trailer

// Autocompletion

