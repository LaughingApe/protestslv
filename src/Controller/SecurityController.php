<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

use App\Entity\Admin;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils, UserPasswordEncoderInterface $encoder): Response
    {
        /*$request = Request::createFromGlobals();
        $entityManager = $this->getDoctrine()->getManager();

        $newAdmin = new Admin();
        $newAdmin->setUsername( "neniks" );
        $newAdmin->setName( "Neniks" );

        $newAdmin->setPassword( $encoder->encodePassword($newAdmin, "neniks") );
        $newAdmin->setRoles( array("ROLE_ADMIN") );

        $entityManager->persist($newAdmin);
        $entityManager->flush();//*/

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('admin/pages/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }
}
