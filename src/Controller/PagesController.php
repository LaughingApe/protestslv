<?php
// src/Controller/PagesController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class PagesController extends AbstractController
{
    public function index(){
        return $this->render('pages/index.html.twig', [
            'controller_name' => 'PagesController',
        ]);//*/
    }
    /*public function number()
    {
        $number = random_int(0, 100);

        return new Response(
            '<html><body>Lucky number: '.$number.'</body></html>'
        );
    }*/
}