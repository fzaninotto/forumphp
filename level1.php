<?php

$dsn = 'mysql:host=localhost;dbname=bookstore';
$user = 'fzaninotto';
$pass = 'S3Cr3t';

try {
  
  // opening the connection
  $con = new PDO($dsn, $user, $pass);
  $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $con->beginTransaction();

  // inserting an author
  $sql = 'INSERT INTO `author` (`first_name`, `last_name`)
    VALUES (:first_name, :last_name)';
  $sth = $con->prepare($sql);
  $sth->bindValue(':first_name', 'Leo', PDO::PARAM_STR);
  $sth->bindValue(':last_name', 'Tolstoi', PDO::PARAM_STR);
  $sth->execute();

  // inserting a couple books
  $author_id = $con->lastInsertId()	;
  $sql = 'INSERT INTO `book` (`title`, `author_id`)
    VALUES (:title, :author_id)';
  $sth = $con->prepare($sql);
  $sth->bindValue(':title', 'War and Peace', PDO::PARAM_STR);
  $sth->bindValue(':author_id', $author_id, PDO::PARAM_INT);
  $sth->execute();
  $sth->bindValue(':title', 'Anna Karenina', PDO::PARAM_STR);
  $sth->bindValue(':author_id', $author_id, PDO::PARAM_INT);
  $sth->execute();
  
  // selecting a book by Tolstoi named 'War%'
  $sql = 'SELECT `book`.`id`, `book`.`title`, `book`.`author_id`
    FROM `book` 
    INNER JOIN `author` ON (`book`.`author_id` = `author`.`id`)
    WHERE `author`.`last_name` = ?
    AND `book`.`title` LIKE ? 
    LIMIT 1';
  $sth = $con->prepare($sql);
  $sth->bindValue(1, 'Tolstoi', PDO::PARAM_STR);
  $sth->bindValue(2, 'War%', PDO::PARAM_STR);
  $sth->execute();
  if ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
    printf('Book of id %d, named %s, written by author of id %d',
      $row['id'], $row['title'], $row['author_id']
    );
  }
  
  // selecting all books
  $sql = 'SELECT `book`.`id`, `book`.`title`, `book`.`author_id`
    FROM `book`';
  $sth = $con->prepare($sql);
  $sth->execute();
  $nbResults = 0;
  while ($row = $sth->fetch(PDO::FETCH_NUM)) {
    $nbResults++;
    printf("Book of id %d, named %s, written by author of id %d\n",
      $row[0], $row[1], $row[2]
    );
  }
  if (!$nbResults) {
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
