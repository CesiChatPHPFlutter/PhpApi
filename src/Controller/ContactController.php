<?php
namespace App\Controller;

use App\Service\MailService;

class ContactController extends AbstractController {

    public function form(){
        return $this->getTwig()->render("Contact/form.html.twig");
    }

    public function send(){
        $mail = new MailService();
        $mail->send(
            from: $_POST["mail"],
            to: "admin@votresite.com",
            subject: "Contact depuis le formulaire principal",
            bodyHtml: $this->getTwig()->render("Mail/contact.html.twig",[
                "nom" => $_POST["nom"],
                "email" => $_POST["mail"],
                "message" => $_POST["message"]
            ])
        );
        header("location:/Contact/form");
    }
}