<?php
namespace src\Model;
class Article implements \JsonSerializable
{
    private ?int $Id = null;
    private ?string $Titre = null;
    private ?string $Description = null;
    private ?string $Auteur = null;
    private ?\DateTime $DatePublication = null;
    private ?string $ImageRepository = null;
    private ?string $ImageFileName = null;

    public function getId(): ?int
    {
        return $this->Id;
    }

    public function setId(?int $Id): Article
    {
        $this->Id = $Id;
        return $this;
    }

    public function getTitre(): ?string
    {
        return $this->Titre;
    }

    public function setTitre(?string $Titre): Article
    {
        $this->Titre = $Titre;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->Description;
    }

    public function setDescription(?string $Description): Article
    {
        $this->Description = $Description;
        return $this;
    }

    public function getAuteur(): ?string
    {
        return $this->Auteur;
    }

    public function setAuteur(?string $Auteur): Article
    {
        $this->Auteur = $Auteur;
        return $this;
    }

    public function getDatePublication(): ?\DateTime
    {
        return $this->DatePublication;
    }

    public function setDatePublication(?\DateTime $DatePublication): Article
    {
        $this->DatePublication = $DatePublication;
        return $this;
    }

    public function getImageRepository(): ?string
    {
        return $this->ImageRepository;
    }

    public function setImageRepository(?string $ImageRepository): Article
    {
        $this->ImageRepository = $ImageRepository;
        return $this;
    }

    public function getImageFileName(): ?string
    {
        return $this->ImageFileName;
    }

    public function setImageFileName(?string $ImageFileName): Article
    {
        $this->ImageFileName = $ImageFileName;
        return $this;
    }

    public function premiersMots(int $n): string
    {
        preg_match('/^(\S+\s+){0,' . ($n - 1) . '}\S+/', $this->Description, $matches);
        $resultat = $matches[0];
        return $resultat;
    }

    public function strlen()
    {
        return \strlen($this->Titre) + 1;
    }

    public function SqlAdd(): array
    {
        try {
            $bdd = BDD::getInstance();
            $requete = $bdd->prepare("INSERT INTO articles (Titre, Description, DatePublication, Auteur, ImageRepository, ImageFileName) VALUES(:Titre, :Description, :DatePublication, :Auteur, :ImageRepository, :ImageFileName)");

            $execute = $requete->execute([
                "Titre" => $this->getTitre(),
                "Description" => $this->getDescription(),
                "DatePublication" => $this->getDatePublication()->format("Y-m-d"),
                "Auteur" => $this->getAuteur(),
                "ImageRepository" => $this->getImageRepository(),
                "ImageFileName" => $this->getImageFileName(),
            ]);
            return [0, "Insertion OK", $bdd->lastInsertId()];
        } catch (\Exception $e) {
            return [1, $e->getMessage()];
        }

    }


    /**
     * @param \PDO $bdd
     * @param int $nb
     * @return array<int,Article>
     */
    public static function SqlGetLast(int $nb): array
    {
        $bdd = BDD::getInstance();
        $requete = $bdd->prepare('SELECT * FROM articles ORDER BY Id DESC LIMIT :nb');
        $requete->bindValue('nb', $nb, \PDO::PARAM_INT);
        $requete->execute();
        $articlesSQL = $requete->fetchAll(\PDO::FETCH_ASSOC);
        $articlesObjet = [];
        foreach ($articlesSQL as $articleSQL) {
            $article = new Article();
            $date = new \DateTime($articleSQL["DatePublication"]);
            $article->setTitre($articleSQL["Titre"])
                ->setId($articleSQL["Id"])
                ->setDescription($articleSQL["Description"])
                ->setDatePublication($date)
                ->setAuteur($articleSQL["Auteur"]);
            $articlesObjet[] = $article;
        }
        return $articlesObjet;
    }

    public static function SqlGetAll()
    {
        $bdd = BDD::getInstance();
        $requete = $bdd->prepare('SELECT * FROM articles ORDER BY Id DESC');
        $requete->execute();
        $articlesSQL = $requete->fetchAll(\PDO::FETCH_ASSOC);
        $articlesObjet = [];
        foreach ($articlesSQL as $articleSQL) {
            $article = new Article();
            $date = new \DateTime($articleSQL["DatePublication"]);
            $article->setTitre($articleSQL["Titre"])
                ->setId($articleSQL["Id"])
                ->setDescription($articleSQL["Description"])
                ->setDatePublication($date)
                ->setAuteur($articleSQL["Auteur"]);
            $articlesObjet[] = $article;
        }
        return $articlesObjet;
    }

    public static function SqlFixtures()
    {
        $bdd = BDD::getInstance();
        $requete = $bdd->prepare('TRUNCATE TABLE articles');
        $requete->execute();
        $arrayAuteur = array('Fabien', 'Brice', 'Bruno', 'Benoit');
        $arrayTitre = array('PHP en force', 'React JS une valeur montante', 'C# toujours au top', 'Java en légère baisse'
        , 'Les entreprises qui recrutent', 'Les formations à ne pas rater', 'Les langages populaires en 2020', 'L\'année du Javascript');
        $dateajout = new \DateTime();
        for ($i = 1; $i <= 200; $i++) {
            shuffle($arrayAuteur);
            shuffle($arrayTitre);

            $dateajout->modify('+1 day');
            $article = new Article();
            $article
                ->setTitre($arrayTitre[0])
                ->setDescription('On sait depuis longtemps que travailler avec du texte lisible et contenant du sens est source de distractions, et empêche de se concentrer sur la mise en page elle-même. L\'avantage du Lorem Ipsum sur un texte générique comme \'Du texte. Du texte. Du texte.\' est qu\'il possède une distribution de lettres plus ou moins normale, et en tout cas comparable avec celle du français standard. De nombreuses suites logicielles de mise en page ou éditeurs de sites Web ont fait du Lorem Ipsum leur faux texte par défaut, et une recherche pour \'Lorem Ipsum\' vous conduira vers de nombreux sites qui n\'en sont encore qu\'à leur phase de construction. Plusieurs versions sont apparues avec le temps, parfois par accident, souvent intentionnellement (histoire d\'y rajouter de petits clins d\'oeil, voire des phrases embarassantes).')
                ->setDatePublication($dateajout)
                ->setAuteur($arrayAuteur[0]);
            $article->sqlAdd();

        }
    }

    public static function SqlDelete(int $id)
    {
        $bdd = BDD::getInstance();
        $req = $bdd->prepare("DELETE FROM articles WHERE Id=:Id");
        $req->execute([
            "Id" => $id
        ]);
    }

    public static function SqlGetById(int $id): ?Article
    {
        $bdd = BDD::getInstance();
        $req = $bdd->prepare("SELECT * FROM articles WHERE Id=:Id");
        $req->execute([
            "Id" => $id
        ]);
        $articleSql = $req->fetch(\PDO::FETCH_ASSOC);
        if ($articleSql != false) {
            $article = new Article();
            $article->setTitre($articleSql["Titre"])
                ->setId($id)
                ->setDescription(($articleSql["Description"]))
                ->setDatePublication(new \DateTime($articleSql["DatePublication"]))
                ->setAuteur($articleSql["Auteur"])
                ->setImageRepository($articleSql["ImageRepository"])
                ->setImageFileName($articleSql["ImageFileName"]);
            return $article;
        }
        return null;
    }

    public function SqlUpdate()
    {
        $bdd = BDD::getInstance();
        try {
            $requete = $bdd->prepare('UPDATE articles SET Titre=:Titre, Description=:Description, DatePublication=:DatePublication, Auteur=:Auteur, ImageRepository=:ImageRepository, ImageFileName=:ImageFileName WHERE Id=:Id');
            $result = $requete->execute([
                'Titre' => $this->getTitre()
                , 'Description' => $this->getDescription()
                , 'DatePublication' => $this->getDatePublication()->format("Y-m-d")
                , 'Auteur' => $this->getAuteur()
                , 'ImageRepository' => $this->getImageRepository()
                , 'ImageFileName' => $this->getImageFileName()
                , 'Id' => $this->getId()
            ]);
            return array(0, "[OK] Mise à jour");
        } catch (\Exception $e) {
            return array(1, "[ERREUR] " . $e->getMessage());

        }

    }

    public function jsonSerialize(): mixed
    {
        return [
            'Id' => $this->Id,
            'Titre' => $this->Titre,
            'Description' => $this->Description,
            'Auteur' => $this->Auteur,
            'DatePublication' => $this->DatePublication->format("Y-m-d"),
            'ImageRepository' => $this->ImageRepository,
            'ImageFileName' => $this->ImageFileName,
        ];
    }
}