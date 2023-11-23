<?php
namespace src\Controller;

use src\Model\Article;

class ArticleController extends AbstractController {

    public  function index(){
        $articles = Article::SqlGetLast(20);
        return $this->getTwig()->render('Article/index.html.twig',[
            "articles" => $articles
        ]);
    }

    public function fixtures(): string{

        Article::SqlFixtures();
        return "<p>Fixtures ok </p>";
    }

    public function all(){
        $articles = Article::SqlGetAll();
        return $this->getTwig()->render('Article/all.html.twig',[
            "articles" => $articles
        ]);
    }

    public function delete(int $id){
        Article::SqlDelete($id);
        header("Location: /?controller=Article&action=all");
    }
}