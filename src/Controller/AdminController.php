<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

use App\Entity\Admin;

class AdminController extends AbstractController{

    private function checkIfLoggedIn(SessionInterface $session){
        if( $session->get('adminId', -1) == -1 ) return false; else return true;
    }

    private function getAdminById($id){
        $repository = $this->getDoctrine()->getRepository(Admin::class);
        $admin = $repository->find($id);
        return $admin;
    }

    /*public function login(SessionInterface $session){
        if( $this->checkIfLoggedIn($session) ) return $this->redirectToRoute('index');

        return $this->render('admin/pages/login.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }*/


    public function index(SessionInterface $session){

        //return new Response('<html><body>Admin page!</body></html>');//*/
        return $this->render('admin/pages/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);//*/
    }

}