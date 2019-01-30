<?php

require_once 'controller/controllerbis.php';
require_once 'model/Book.php';
require_once 'framework/View.php';
require_once 'framework/Controller.php';
require_once 'useful/ToolsBis.php';

class ControllerBook extends ControllerBis {

    public function basket() {
        $user = $this->get_user_or_redirect();
        $all_books = Book::get_books();
        (new View("basket"))->show(array("user" => $user, "books" => $all_books));
    }

    const UPLOAD_ERR_OK = 0;

    public function add_edit_book() {
        $errors = [];
        $user = $this->get_user_or_redirect();
        $is_admin = $user->is_admin();
        if (isset($_GET['param1'])) {
            $id = trim($_GET['param1']);
            $book = Book::get_by_id($id);
            if (!$book) {
                ToolsBis::abort('Unknown book');
            }
            $isbn = $book->isbn;
            $title = $book->title;
            $author = $book->author;
            $editor = $book->editor;
            $picture_path = $book->picture;
            $titlePage = "Edit";
        } else {
            $id = null;
            $isbn = '';
            $title = '';
            $author = '';
            $editor = '';
            $picture_path = '';
            $titlePage = "Add new";
        }
        if ($this->isMember() || $this->isManager()) {
            $view = true;
            $titlePage = "View";
        } else {
            $view = false;
        }

        if (ToolsBis::check_fields(['cancel'])) {
            $this->redirect("book", "basket");
        }
        if (ToolsBis::check_fields(['save', 'isbn', 'title', 'author', 'editor']) && !$view) {
            //&& ($user->is_admin() || ToolsBis::check_fields(['role'])) à quoi sert elle ???
            $isbn = trim($_POST['isbn']);
            $title = trim($_POST['title']);
            $author = trim($_POST['author']);
            $editor = trim($_POST['editor']);
            $errors = Book::validateBook($id, $isbn, $title, $author, $editor);
            if (isset($_FILES['picture']) && $_FILES['picture']['error'] === self::UPLOAD_ERR_OK) {
                $errors = ToolsBis::validate_photo($_FILES['picture']);
                if (empty($errors)) {
                    $saveTo = ToolsBis::generate_photo_name($_FILES['picture']);
                    $oldFileName = $picture_path;
                    if ($oldFileName && file_exists("picture/" . $oldFileName)) {
                        unlink("picture/" . $oldFileName);
                    }
                    move_uploaded_file($_FILES['picture']['tmp_name'], "picture/$saveTo");
                    $picture_path = "picture/$saveTo";
                }
            }


            if (count($errors) === 0) {
                if ($is_new) {
                    Book::add_book($isbn, $title, $author, $editor, $picture_path);
                } else {
                    Book::edit_book($id, $isbn, $title, $author, $editor, $picture_path);
                }
                $this->redirect("book", "basket");
            }
        }
        (new View("add_edit_book"))->show(array("isbn" => $isbn, "title" => $title,
            "author" => $author, "editor" => $editor, "picture" => $picture_path,
            "errors" => $errors, "view" => $view, "titlePage" => $titlePage,"is_admin" => $is_admin));
    }

    public function delete_book() {
        $user = $this->get_user_or_redirect();
        $this->check_admin();
        $errors = [];
        $book_del = '';

        if (isset($_GET['param1'])) {
            $id = trim($_GET['param1']);
            $book_del = book::get_by_id($id);

            if (!$book_del) {
                ToolsBis::abort('Unknown book');
            }
            $book_del = $book_del->title;
        } else {
            $this->redirect("book", "basket");
        }

        if (isset($_POST['confirm'])) {
            Book::del_book_by_id($id);
            $this->redirect("book", "basket");
        } elseif (isset($_POST['cancel'])) {
            $this->redirect("book", "basket");
        }
        (new View("delete_book"))->show(array("user" => $user, "book_del" => $book_del, "errors" => $errors));
    }

}
