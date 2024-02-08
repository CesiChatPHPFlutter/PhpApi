<?php
namespace App\Controller;

use Mpdf\Mpdf;
use Mpdf\Output\Destination;
use App\Model\Article;
use App\Service\MailService;

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
        $token = md5(random_bytes(32));
        $_SESSION["token"] = $token;
        $articles = Article::SqlGetAll();
        return $this->getTwig()->render('Article/all.html.twig',[
            "articles" => $articles,
            "tokenCSRF" => $token
        ]);
    }

    public function delete(){
        UserController::protect(["Redacteur", "Administrateur", "Editeur"]);
        if(isset($_POST["id"])){
            if($_SESSION["token"] == $_POST["tokenCSRF"]){
                Article::SqlDelete($_POST["id"]);
            }
        }
        header("Location: /Article/all");
    }

    public function add(){
        UserController::protect(["Redacteur", "Administrateur", "Editeur"]);
        if(isset($_POST["Titre"]) && isset($_POST["Description"])){
            $sqlRepository = null;
            $nomImage = null;


            if(!empty($_FILES["Image"]["name"])){
                $tabExt = ["jpg", "jpeg", "gif", "png"]; // Extension autorisée
                $extension = pathinfo($_FILES["Image"]["name"], PATHINFO_EXTENSION);
                if(in_array(strtolower($extension), $tabExt)){
                    //Fabriquer le répertoire d'auccil façon Wrodpress (YYYY/MM)
                    $dateNow = new \DateTime();
                    $repository = "./uploads/images/{$dateNow->format("Y/m")}";
                    if(!is_dir($repository)) {
                        mkdir($repository, 0777, true);
                    }
                    $sqlRepository = $dateNow->format("Y/m");
                    //Renommer le fichier image à la volée
                    $nomImage = md5(uniqid()).".".$extension;
                    var_dump($repository);
                    var_dump($nomImage);
                    die();
                    //Upload du fichier
                    move_uploaded_file($_FILES["Image"]["tmp_name"], $repository."/".$nomImage);
                }
            }

           $article = new Article();
            $article->setTitre($_POST["Titre"])
                ->setDescription($_POST["Description"])
                ->setDatePublication(new \DateTime($_POST["DatePublication"]))
                ->setAuteur($_POST["Auteur"])
                ->setImageRepository($sqlRepository)
                ->setImageFileName($nomImage);
            $result = $article->SqlAdd();

            //Envoi du mail
            $article->setId($result[2]);
            $mail = new MailService();
            $mail->send(
                from: "admin@votresite.com",
                to: "admin@votresite.com",
                subject: "Un nouvel article a été posté",
                bodyHtml: $this->getTwig()->render("Mail/article.add.html.twig",[
                    "article" => $article
                ])
            );

            header("Location: /Article/all");
        }
        return $this->getTwig()->render("Article/add.html.twig");
    }

    public function show(int $id){
        $article = Article::SqlGetById($id);
        if($article==null){
            header("Location: /Article/all");
        }
        return $this->getTwig()->render("Article/show.html.twig", [
            "article" => $article
        ]);
    }

    public function update(int $id){
        $article = Article::SqlGetById($id);
        if($article!=null){
            if(isset($_POST["Titre"]) && isset($_POST["Description"]) && isset($_POST["DatePublication"]) && isset($_POST["Auteur"]) ) {
                // Repris de la version "classic"
                $sqlRepository = null;
                $nomImage = null;

                if(!empty($_FILES['Image']['name']) ) {
                    $tabExt = ['jpg','gif','png','jpeg'];    // Extensions autorisees
                    $extension  = pathinfo($_FILES['Image']['name'], PATHINFO_EXTENSION);
                    // strtolower = on compare ce qui est comparage (JPEG =! jpeg)
                    if(in_array(strtolower($extension),$tabExt)) {
                        // Fabrication du répertoire d'accueil façon "Wordpress" (YYYY/MM)
                        $dateNow = new \DateTime();
                        $sqlRepository = $dateNow->format('Y/m');
                        $repository = './uploads/images/'.$dateNow->format('Y/m');
                        if(!is_dir($repository)){
                            mkdir($repository,0777,true);
                        }
                        // Renommage du fichier (d'où l'intéret d'avoir isolé l'extension
                        $nomImage = md5(uniqid()) .'.'. $extension;

                        //Upload du fichier, voilà c'est fini !
                        move_uploaded_file($_FILES['Image']['tmp_name'], $repository.'/'.$nomImage);

                        // suppression ancienne image si existante
                        if($_POST['imageAncienne'] != '' && $_POST['imageAncienne'] != '/' && file_exists("../../public/uploads/images/{$_POST["imageAncienne"]}")){
                            unlink("./uploads/images/{$_POST['imageAncienne']}");
                        }
                    }
                }

                //On réutilise l'objet Article créé au début de la méthode
                $date = new \DateTime($_POST["DatePublication"]);
                $article->setTitre($_POST["Titre"])
                    ->setDescription($_POST["Description"])
                    ->setDatePublication($date)
                    ->setAuteur($_POST["Auteur"])
                    ->setImageRepository($sqlRepository)
                    ->setImageFileName($nomImage);
                $result = $article->SqlUpdate();

                if($result[0]=="1"){
                    if($nomImage !=null){
                        unlink($repository.'/'.$nomImage);
                    }
                }

                header("Location:/Article/update/{$id}");
            }else{
                return $this->getTwig()->render('Article/update.html.twig',[
                    "article"=>$article
                ]);
            }

        }else{
            header("Location:/Article/all");

        }
    }

    public function pdf(int $id){
        $article = Article::SqlGetById($id);
        $mpdf = new Mpdf([
            "tempDir" => $_SERVER["DOCUMENT_ROOT"]."/../var/cache/pdf"
        ]);
        $mpdf->WriteHTML($this->getTwig()->render("Article/pdf.html.twig", [
            "article" => $article
        ]));
        $mpdf->Output(name: "Article.pdf",dest: Destination::DOWNLOAD);
    }


}